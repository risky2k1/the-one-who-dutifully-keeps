<?php

namespace Botble\Kids\Models;

use Botble\Base\Models\BaseModel;
use Botble\Kids\Enums\TopicStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KidsTopic extends BaseModel
{
    protected $table = 'kids_topics';

    protected $fillable = [
        'parent_id',
        'grade',
        'code',
        'name',
        'generator',
        'generator_config',
        'status',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'grade' => 'integer',
            'generator_config' => 'array',
            'status' => TopicStatusEnum::class,
            'order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(KidsTopic::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(KidsTopic::class, 'parent_id')->orderBy('order');
    }

    public function wordTemplates(): HasMany
    {
        return $this->hasMany(KidsWordTemplate::class, 'topic_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', TopicStatusEnum::APPROVED);
    }

    public function isLeaf(): bool
    {
        return $this->generator !== null;
    }
}
