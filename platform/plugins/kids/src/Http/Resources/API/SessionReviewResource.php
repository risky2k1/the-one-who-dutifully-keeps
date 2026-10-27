<?php

namespace Botble\Kids\Http\Resources\API;

use Botble\Kids\Models\KidsSessionQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'assignment_id' => $this->assignment_id,
            'expires_at' => $this->expires_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'first_try_correct' => (int) $this->first_try_correct,
            'total' => (int) $this->total,
            'questions' => $this->questions->map(function (KidsSessionQuestion $q) {
                return [
                    'id' => $q->getKey(),
                    'position' => (int) $q->position,
                    'topic_id' => (int) $q->topic_id,
                    'prompt' => $q->prompt,
                    'attempts' => (int) $q->attempts,
                    'state' => (string) ($q->state?->getValue() ?? $q->state),
                    'answer' => $q->toQuestion()->answer->display(),
                    'solution' => $q->solution ?? [],
                    'given_answers' => $q->given_answers ?? [],
                    'first_try_correct' => $q->first_try_correct !== null ? (bool) $q->first_try_correct : null,
                ];
            })->all(),
        ];
    }
}
