<?php

namespace Botble\Kids\Services;

use Botble\Kids\Enums\SessionStatusEnum;
use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsSession;
use Botble\Kids\Models\KidsSessionQuestion;

class ResultsService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function assignmentResults(KidsChild $child, ?int $assignmentId = null): array
    {
        $query = $child->assignments();

        if ($assignmentId) {
            $query->where('id', $assignmentId);
        }

        $assignments = $query->get();
        $results = [];

        foreach ($assignments as $assignment) {
            $completedSessions = $assignment->sessions()
                ->where('status', SessionStatusEnum::COMPLETED)
                ->orderBy('completed_at', 'asc')
                ->get();

            $firstSession = $completedSessions->first();
            $latestSession = $completedSessions->last();

            $results[] = [
                'assignment_id' => $assignment->getKey(),
                'title' => $assignment->title,
                'attempts_count' => $completedSessions->count(),
                'first' => $firstSession ? $this->formatScoreLine($firstSession) : null,
                'latest' => $latestSession ? $this->formatScoreLine($latestSession) : null,
            ];
        }

        return $results;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function weakSkills(KidsChild $child): array
    {
        $questions = KidsSessionQuestion::query()
            ->whereHas('session', fn ($q) => $q->where('child_id', $child->getKey()))
            ->whereNotNull('first_try_correct')
            ->where('created_at', '>=', now()->subDays(30))
            ->with('topic')
            ->get()
            ->groupBy('topic_id');

        $weak = [];
        $levels = $child->skillLevels()->pluck('level', 'topic_id')->all();

        foreach ($questions as $topicId => $topicQuestions) {
            $answered = $topicQuestions->count();
            if ($answered < 5) {
                continue;
            }

            $correct = $topicQuestions->where('first_try_correct', true)->count();
            $accuracy = round($correct / $answered, 4);

            if ($accuracy >= 0.7) {
                continue;
            }

            $topic = $topicQuestions->first()->topic;
            $topicName = $topic ? $topic->name : "Topic #{$topicId}";
            $level = (int) ($levels[$topicId] ?? 1);

            $weak[] = [
                'topic_id' => $topicId,
                'name' => $topicName,
                'first_try_accuracy' => $accuracy,
                'answered' => $answered,
                'level' => $level,
            ];
        }

        usort($weak, fn ($a, $b) => $a['first_try_accuracy'] <=> $b['first_try_accuracy']);

        return $weak;
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatScoreLine(KidsSession $session): array
    {
        $duration = ($session->started_at && $session->completed_at)
            ? abs((int) $session->completed_at->diffInSeconds($session->started_at))
            : 0;

        return [
            'session_id' => $session->getKey(),
            'first_try_correct' => (int) $session->first_try_correct,
            'total' => (int) $session->total,
            'duration_seconds' => $duration,
            'completed_at' => $session->completed_at?->toISOString(),
        ];
    }
}
