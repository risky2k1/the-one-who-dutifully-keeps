<?php

namespace Botble\Kids\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'grade' => $this->grade,
            'code' => $this->code,
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'is_leaf' => $this->isLeaf(),
            'level' => $this->when(isset($this->current_level), $this->current_level, null),
        ];
    }
}
