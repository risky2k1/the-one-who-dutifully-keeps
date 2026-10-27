<?php

namespace Botble\Kids\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'assignment_id' => $this->resource['assignment_id'] ?? $this->assignment_id,
            'title' => $this->resource['title'] ?? $this->title,
            'attempts_count' => (int) ($this->resource['attempts_count'] ?? $this->attempts_count),
            'first' => $this->resource['first'] ?? $this->first,
            'latest' => $this->resource['latest'] ?? $this->latest,
        ];
    }
}
