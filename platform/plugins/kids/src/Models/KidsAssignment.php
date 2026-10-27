<?php

namespace Botble\Kids\Models;

use Botble\Base\Models\BaseModel;
use Botble\Kids\Enums\AssignmentDifficultyEnum;
use Botble\Kids\Enums\RecurrenceEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KidsAssignment extends BaseModel
{
    protected $table = 'kids_assignments';

    protected $fillable = [
        'parent_id',
        'child_id',
        'title',
        'topic_ids',
        'question_count',
        'difficulty',
        'due_at',
        'time_limit_seconds',
        'recurrence',
    ];

    protected function casts(): array
    {
        return [
            'topic_ids' => 'array',
            'question_count' => 'integer',
            'difficulty' => AssignmentDifficultyEnum::class,
            'due_at' => 'datetime',
            'time_limit_seconds' => 'integer',
            'recurrence' => RecurrenceEnum::class,
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(KidsParent::class, 'parent_id');
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(KidsChild::class, 'child_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(KidsSession::class, 'assignment_id');
    }
}
