<?php

namespace Botble\Kids\Http\Controllers\API\Kid;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Kids\Enums\SessionStatusEnum;
use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Kids\Http\Resources\API\ChildResource;
use Botble\Kids\Http\Resources\API\RewardResource;
use Botble\Kids\Http\Resources\API\TopicResource;
use Botble\Kids\Models\KidsAssignment;
use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsTopic;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends BaseApiController
{
    public function index(Request $request): mixed
    {
        /** @var KidsChild $child */
        $child = $request->user();

        $tz = config('plugins.kids.general.timezone', 'Asia/Ho_Chi_Minh');
        $today = Carbon::now($tz)->toDateString();

        $pendingAssignments = [];
        foreach ($child->assignments as $a) {
            /** @var KidsAssignment $a */
            $rec = (string) ($a->recurrence?->getValue() ?? $a->recurrence);
            $hasCompleted = false;

            if ($rec === 'daily') {
                $hasCompleted = $a->sessions()
                    ->where('status', SessionStatusEnum::COMPLETED)
                    ->whereDate('completed_at', $today)
                    ->exists();
            } else {
                $hasCompleted = $a->sessions()
                    ->where('status', SessionStatusEnum::COMPLETED)
                    ->exists();
            }

            if (! $hasCompleted) {
                $isNew = ! $a->sessions()->exists();
                $pendingAssignments[] = [
                    'id' => $a->getKey(),
                    'title' => $a->title,
                    'question_count' => (int) $a->question_count,
                    'due_at' => $a->due_at?->toISOString(),
                    'is_new' => $isNew,
                ];
            }
        }

        $practiceLocked = (bool) ($child->assigned_first && count($pendingAssignments) > 0);

        return $this->httpResponse()->setData([
            'child' => (new ChildResource($child))->resolve(),
            'pending_assignments' => $pendingAssignments,
            'streak_current' => (int) $child->streak_current,
            'practice_locked' => $practiceLocked,
        ])->toApiResponse();
    }

    public function topics(Request $request): mixed
    {
        /** @var KidsChild $child */
        $child = $request->user();

        $disabled = $child->disabled_topic_ids ?? [];
        $skillLevels = $child->skillLevels()->pluck('level', 'topic_id')->all();

        $topics = KidsTopic::query()
            ->where('grade', $child->grade)
            ->where('status', TopicStatusEnum::APPROVED)
            ->whereNotNull('generator')
            ->when(! empty($disabled), fn ($q) => $q->whereNotIn('id', $disabled))
            ->orderBy('id')
            ->get()
            ->each(function (KidsTopic $topic) use ($skillLevels) {
                $topic->current_level = (int) ($skillLevels[$topic->getKey()] ?? 1);
            });

        return $this->httpResponse()->setData(TopicResource::collection($topics))->toApiResponse();
    }

    public function rewards(Request $request): mixed
    {
        /** @var KidsChild $child */
        $child = $request->user();

        $rewards = $child->rewards()->orderByDesc('created_at')->get();

        return $this->httpResponse()->setData(RewardResource::collection($rewards))->toApiResponse();
    }
}
