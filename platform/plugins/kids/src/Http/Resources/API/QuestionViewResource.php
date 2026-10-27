<?php

namespace Botble\Kids\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionViewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'position' => $this->position,
            'topic_id' => $this->topic_id,
            'prompt' => $this->prompt,
            'attempts' => $this->attempts,
            'state' => (string) ($this->state?->getValue() ?? $this->state),
        ];
    }
}
