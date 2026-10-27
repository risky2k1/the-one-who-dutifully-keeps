<?php

namespace Botble\Kids\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'child_id' => $this->child_id,
            'title' => $this->title,
            'topic_ids' => $this->topic_ids,
            'question_count' => $this->question_count,
            'difficulty' => (string) ($this->difficulty?->getValue() ?? $this->difficulty),
            'due_at' => $this->due_at?->toISOString(),
            'time_limit_seconds' => $this->time_limit_seconds,
            'recurrence' => (string) ($this->recurrence?->getValue() ?? $this->recurrence),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
