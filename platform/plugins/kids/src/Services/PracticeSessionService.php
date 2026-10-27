<?php

namespace Botble\Kids\Services;

use Botble\Kids\Engine\Answer;
use Botble\Kids\Engine\GeneratorRegistry;
use Botble\Kids\Engine\Question;
use Botble\Kids\Enums\SessionStatusEnum;
use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Kids\Models\KidsAssignment;
use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsReviewItem;
use Botble\Kids\Models\KidsSession;
use Botble\Kids\Models\KidsSessionQuestion;
use Botble\Kids\Models\KidsTopic;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Random\Engine\Mt19937;
use Random\Randomizer;

class PracticeSessionService
{
    public function __construct(
        protected GeneratorRegistry $registry
    ) {}

    public function start(KidsChild $child, ?KidsTopic $topic = null, ?KidsAssignment $assignment = null): KidsSession
    {
        if ($assignment !== null) {
            return $this->startAssignmentSession($child, $assignment);
        }

        if ($topic !== null) {
            return $this->startSelfPracticeSession($child, $topic);
        }

        throw new \InvalidArgumentException('Either topic or assignment must be provided.');
    }

    protected function startSelfPracticeSession(KidsChild $child, KidsTopic $topic): KidsSession
    {
        if ($topic->grade !== $child->grade) {
            throw ValidationException::withMessages(['topic_id' => ['Chủ đề không thuộc lớp học của bé.']]);
        }

        $topicStatus = (string) ($topic->status?->getValue() ?? $topic->status);
        if ($topicStatus !== TopicStatusEnum::APPROVED) {
            throw ValidationException::withMessages(['topic_id' => ['Chủ đề chưa được phê duyệt.']]);
        }

        if (! $topic->isLeaf()) {
            throw ValidationException::withMessages(['topic_id' => ['Chủ đề không phải là chủ đề luyện tập.']]);
        }

        $disabled = $child->disabled_topic_ids ?? [];
        if (in_array($topic->getKey(), $disabled)) {
            throw ValidationException::withMessages(['topic_id' => ['Chủ đề này đã bị phụ huynh tạm khóa cho bé.']]);
        }

        // Check assigned_first
        if ($child->assigned_first) {
            $tz = config('plugins.kids.general.timezone', 'Asia/Ho_Chi_Minh');
            $today = Carbon::now($tz)->toDateString();

            $pending = $child->assignments->first(function (KidsAssignment $a) use ($today): bool {
                $rec = (string) ($a->recurrence?->getValue() ?? $a->recurrence);
                if ($rec === 'daily') {
                    return ! $a->sessions()
                        ->where('status', SessionStatusEnum::COMPLETED)
                        ->whereDate('completed_at', $today)
                        ->exists();
                }

                return ! $a->sessions()
                    ->where('status', SessionStatusEnum::COMPLETED)
                    ->exists();
            });

            if ($pending) {
                abort(423, 'Vui lòng hoàn thành bài tập được giao trước khi tự luyện tập.');
            }
        }

        $seed = random_int(100000, 999999999);
        /** @var KidsSession $session */
        $session = KidsSession::query()->create([
            'child_id' => $child->getKey(),
            'seed' => $seed,
            'status' => SessionStatusEnum::IN_PROGRESS,
            'started_at' => now(),
            'total' => 10,
        ]);

        $dueReviews = KidsReviewItem::query()
            ->where('child_id', $child->getKey())
            ->where('topic_id', $topic->getKey())
            ->where('due_at', '<=', now())
            ->take(3)
            ->get();

        $position = 1;
        $seenPrompts = [];

        // 1. Add review questions
        foreach ($dueReviews as $reviewItem) {
            $rawQ = $reviewItem->question;
            $ans = new Answer(
                $rawQ['answer']['kind'] ?? 'integer',
                (string) ($rawQ['answer']['value'] ?? ''),
                (bool) ($rawQ['answer']['requireSimplest'] ?? false)
            );

            $engineQ = new Question(
                $rawQ['type'],
                $rawQ['prompt'],
                $ans,
                $rawQ['hints'] ?? [],
                $rawQ['solution'] ?? [],
                $rawQ['params'] ?? []
            );

            $sessionQuestion = new KidsSessionQuestion([
                'session_id' => $session->getKey(),
                'position' => $position++,
                'topic_id' => $topic->getKey(),
                'review_item_id' => $reviewItem->getKey(),
                'level' => (int) ($rawQ['params']['level'] ?? 1),
            ]);
            $sessionQuestion->fillFromQuestion($engineQ);
            $sessionQuestion->save();

            $seenPrompts[] = json_encode($engineQ->prompt);
        }

        // 2. Generate remaining questions
        $level = (int) ($child->skillLevels()->where('topic_id', $topic->getKey())->value('level') ?? 1);
        $generator = $this->registry->get($topic->generator);
        $config = $topic->generator_config ?? [];

        if ($topic->generator === 'word_problem') {
            $templates = $topic->wordTemplates()->where('status', TopicStatusEnum::APPROVED)->get()->toArray();
            $config['templates'] = $templates;
        }

        while ($position <= 10) {
            $engineQ = null;
            for ($attempt = 0; $attempt < 20; $attempt++) {
                $rng = new Randomizer(new Mt19937($seed + ($position * 100) + $attempt));
                $candidate = $generator->generate($level, $rng, $config);
                $sig = json_encode($candidate->prompt);
                if (! in_array($sig, $seenPrompts)) {
                    $engineQ = $candidate;
                    $seenPrompts[] = $sig;
                    break;
                }
            }

            if (! $engineQ) {
                $rng = new Randomizer(new Mt19937($seed + ($position * 100)));
                $engineQ = $generator->generate($level, $rng, $config);
            }

            $sessionQuestion = new KidsSessionQuestion([
                'session_id' => $session->getKey(),
                'position' => $position++,
                'topic_id' => $topic->getKey(),
                'level' => $level,
            ]);
            $sessionQuestion->fillFromQuestion($engineQ);
            $sessionQuestion->save();
        }

        return $session->load('questions');
    }

    protected function startAssignmentSession(KidsChild $child, KidsAssignment $assignment): KidsSession
    {
        $seed = random_int(100000, 999999999);
        $expiresAt = $assignment->time_limit_seconds ? now()->addSeconds($assignment->time_limit_seconds) : null;
        $total = $assignment->question_count ?: 10;

        /** @var KidsSession $session */
        $session = KidsSession::query()->create([
            'child_id' => $child->getKey(),
            'assignment_id' => $assignment->getKey(),
            'seed' => $seed,
            'status' => SessionStatusEnum::IN_PROGRESS,
            'started_at' => now(),
            'expires_at' => $expiresAt,
            'total' => $total,
        ]);

        $topicIds = $assignment->topic_ids;
        $topics = KidsTopic::query()->whereIn('id', $topicIds)->get()->keyBy('id');
        $seenPrompts = [];
        $diffSetting = (string) ($assignment->difficulty?->getValue() ?? $assignment->difficulty);

        for ($pos = 1; $pos <= $total; $pos++) {
            $topicId = $topicIds[($pos - 1) % count($topicIds)];
            /** @var KidsTopic $topic */
            $topic = $topics->get($topicId);

            $level = match ($diffSetting) {
                'easy' => random_int(1, 2),
                'medium' => 3,
                'hard' => random_int(4, 5),
                default => (int) ($child->skillLevels()->where('topic_id', $topic->getKey())->value('level') ?? 1),
            };

            $generator = $this->registry->get($topic->generator);
            $config = $topic->generator_config ?? [];

            if ($topic->generator === 'word_problem') {
                $templates = $topic->wordTemplates()->where('status', TopicStatusEnum::APPROVED)->get()->toArray();
                $config['templates'] = $templates;
            }

            $engineQ = null;
            for ($attempt = 0; $attempt < 20; $attempt++) {
                $rng = new Randomizer(new Mt19937($seed + ($pos * 100) + $attempt));
                $candidate = $generator->generate($level, $rng, $config);
                $sig = json_encode($candidate->prompt);
                if (! in_array($sig, $seenPrompts)) {
                    $engineQ = $candidate;
                    $seenPrompts[] = $sig;
                    break;
                }
            }

            if (! $engineQ) {
                $rng = new Randomizer(new Mt19937($seed + ($pos * 100)));
                $engineQ = $generator->generate($level, $rng, $config);
            }

            $sessionQuestion = new KidsSessionQuestion([
                'session_id' => $session->getKey(),
                'position' => $pos,
                'topic_id' => $topic->getKey(),
                'level' => $level,
            ]);
            $sessionQuestion->fillFromQuestion($engineQ);
            $sessionQuestion->save();
        }

        return $session->load('questions');
    }
}
