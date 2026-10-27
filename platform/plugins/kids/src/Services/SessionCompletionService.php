<?php

namespace Botble\Kids\Services;

use Botble\Kids\Engine\DifficultyAdjuster;
use Botble\Kids\Engine\ReviewScheduler;
use Botble\Kids\Enums\QuestionStateEnum;
use Botble\Kids\Enums\SessionStatusEnum;
use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsReviewItem;
use Botble\Kids\Models\KidsReward;
use Botble\Kids\Models\KidsSession;
use Botble\Kids\Models\KidsSessionQuestion;
use Botble\Kids\Models\KidsSkillLevel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SessionCompletionService
{
    /**
     * @return array{session_id: int|string, first_try_correct: int, total: int, duration_seconds: int, reward: array{id: int|string, sticker_key: string, earned_at: string}|null, streak_current: int}
     */
    public function complete(KidsSession $session): array
    {
        $sessionStatus = (string) ($session->status?->getValue() ?? $session->status);

        if ($sessionStatus === SessionStatusEnum::COMPLETED) {
            $session->loadMissing(['child', 'reward']);
            $duration = ($session->started_at && $session->completed_at)
                ? abs((int) $session->completed_at->diffInSeconds($session->started_at))
                : 0;

            return [
                'session_id' => $session->getKey(),
                'first_try_correct' => (int) $session->first_try_correct,
                'total' => (int) $session->total,
                'duration_seconds' => $duration,
                'reward' => $session->reward ? [
                    'id' => $session->reward->getKey(),
                    'sticker_key' => $session->reward->sticker_key,
                    'earned_at' => $session->reward->created_at?->toISOString() ?? now()->toISOString(),
                ] : null,
                'streak_current' => (int) $session->child->streak_current,
            ];
        }

        return DB::transaction(function () use ($session): array {
            /** @var KidsChild $child */
            $child = $session->child()->lockForUpdate()->firstOrFail();

            // 1. Any remaining open question counts as first_try_correct = false
            $openQuestions = $session->questions()->whereNull('first_try_correct')->get();
            foreach ($openQuestions as $openQ) {
                $openQ->first_try_correct = false;
                $openQ->state = QuestionStateEnum::REVEALED;
                $openQ->save();
            }

            $firstTryCorrect = $session->questions()->where('first_try_correct', true)->count();
            $completedAt = now();

            $session->status = SessionStatusEnum::COMPLETED;
            $session->completed_at = $completedAt;
            $session->first_try_correct = $firstTryCorrect;
            $session->save();

            // 2. Update skill levels
            $questions = $session->questions()->get();
            $topicIds = $questions->pluck('topic_id')->unique()->values()->all();
            foreach ($topicIds as $topicId) {
                /** @var KidsSkillLevel $skillLevel */
                $skillLevel = KidsSkillLevel::query()->firstOrCreate(
                    ['child_id' => $child->getKey(), 'topic_id' => $topicId],
                    ['level' => 1, 'level_changed_at' => $child->created_at ?? now()]
                );

                $results = KidsSessionQuestion::query()
                    ->whereHas('session', fn ($q) => $q->where('child_id', $child->getKey()))
                    ->where('topic_id', $topicId)
                    ->whereNotNull('first_try_correct')
                    ->where('created_at', '>=', $skillLevel->level_changed_at)
                    ->orderBy('created_at', 'desc')
                    ->pluck('first_try_correct')
                    ->all();

                $nextLevel = DifficultyAdjuster::next($skillLevel->level, $results);
                if ($nextLevel !== $skillLevel->level) {
                    $skillLevel->level = $nextLevel;
                    $skillLevel->level_changed_at = now();
                    $skillLevel->save();
                }
            }

            // 3. Review items
            $nowImmutable = now()->toImmutable();
            $questions = $session->questions()->get();

            foreach ($questions as $q) {
                if ($q->review_item_id) {
                    $reviewItem = KidsReviewItem::query()->find($q->review_item_id);
                    if ($reviewItem) {
                        $res = ReviewScheduler::afterAnswer($reviewItem->stage, (bool) $q->first_try_correct, $nowImmutable);
                        if ($res['stage'] === null) {
                            $reviewItem->delete();
                        } else {
                            $reviewItem->update(['stage' => $res['stage'], 'due_at' => $res['due_at']]);
                        }
                    }
                } elseif ($q->first_try_correct === false) {
                    $res = ReviewScheduler::afterAnswer(null, false, $nowImmutable);
                    KidsReviewItem::query()->create([
                        'child_id' => $child->getKey(),
                        'topic_id' => $q->topic_id,
                        'question' => [
                            'type' => $q->type,
                            'prompt' => $q->prompt,
                            'answer' => $q->answer,
                            'hints' => $q->hints,
                            'solution' => $q->solution,
                            'params' => $q->params,
                        ],
                        'stage' => $res['stage'],
                        'due_at' => $res['due_at'],
                    ]);
                }
            }

            // 4. Update streak
            $tz = config('plugins.kids.general.timezone', 'Asia/Ho_Chi_Minh');
            $today = Carbon::now($tz)->toDateString();
            $yesterday = Carbon::now($tz)->subDay()->toDateString();
            $lastPracticed = $child->last_practiced_on ? Carbon::parse($child->last_practiced_on)->toDateString() : null;

            if ($lastPracticed === $today) {
                // keep streak
            } elseif ($lastPracticed === $yesterday) {
                $child->streak_current = $child->streak_current + 1;
            } else {
                $child->streak_current = 1;
            }

            if ($child->streak_current > $child->streak_best) {
                $child->streak_best = $child->streak_current;
            }
            $child->last_practiced_on = $today;
            $child->save();

            // 5. Award reward sticker if >= 60% first try correct
            $reward = null;
            $totalQuestions = max(1, (int) $session->total);
            if (($firstTryCorrect / $totalQuestions) >= 0.6) {
                $owned = $child->rewards()->pluck('sticker_key')->all();
                $allStickers = config('plugins.kids.general.stickers', ['star-gold', 'rocket', 'crown']);
                $unowned = array_values(array_diff($allStickers, $owned));
                $pool = ! empty($unowned) ? $unowned : $allStickers;
                $stickerKey = $pool[array_rand($pool)];

                $reward = KidsReward::query()->create([
                    'child_id' => $child->getKey(),
                    'session_id' => $session->getKey(),
                    'sticker_key' => $stickerKey,
                ]);
            }

            $duration = $session->started_at ? abs((int) $completedAt->diffInSeconds($session->started_at)) : 0;

            return [
                'session_id' => $session->getKey(),
                'first_try_correct' => $firstTryCorrect,
                'total' => (int) $session->total,
                'duration_seconds' => $duration,
                'reward' => $reward ? [
                    'id' => $reward->getKey(),
                    'sticker_key' => $reward->sticker_key,
                    'earned_at' => $reward->created_at?->toISOString() ?? now()->toISOString(),
                ] : null,
                'streak_current' => (int) $child->streak_current,
            ];
        });
    }
}
