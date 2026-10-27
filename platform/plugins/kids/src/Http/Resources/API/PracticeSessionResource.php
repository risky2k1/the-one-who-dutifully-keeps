<?php

namespace Botble\Kids\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PracticeSessionResource extends JsonResource
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
            'questions' => QuestionViewResource::collection($this->questions),
        ];
    }
}
