<?php

namespace Botble\Kids\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChildResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
            'avatar' => $this->avatar,
            'grade' => $this->grade,
            'textbook' => (string) ($this->textbook?->getValue() ?? $this->textbook),
            'disabled_topic_ids' => $this->disabled_topic_ids ?? [],
            'assigned_first' => (bool) $this->assigned_first,
            'streak_current' => $this->streak_current,
            'streak_best' => $this->streak_best,
        ];
    }
}
