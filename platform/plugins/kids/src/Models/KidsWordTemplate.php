<?php

namespace Botble\Kids\Models;

use Botble\Base\Models\BaseModel;
use Botble\Kids\Enums\TopicStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KidsWordTemplate extends BaseModel
{
    protected $table = 'kids_word_templates';

    protected $fillable = [
        'topic_id',
        'body',
        'op',
        'unit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => TopicStatusEnum::class,
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(KidsTopic::class, 'topic_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', TopicStatusEnum::APPROVED);
    }
}
