<?php

namespace Botble\Kids\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeakSkillResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'topic_id' => $this->resource['topic_id'] ?? $this->topic_id,
            'name' => $this->resource['name'] ?? $this->name,
            'first_try_accuracy' => (float) ($this->resource['first_try_accuracy'] ?? $this->first_try_accuracy),
            'answered' => (int) ($this->resource['answered'] ?? $this->answered),
            'level' => (int) ($this->resource['level'] ?? $this->level),
        ];
    }
}
