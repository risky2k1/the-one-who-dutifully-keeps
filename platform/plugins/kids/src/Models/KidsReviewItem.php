<?php

namespace Botble\Kids\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KidsReviewItem extends BaseModel
{
    protected $table = 'kids_review_items';

    protected $fillable = [
        'child_id',
        'topic_id',
        'question',
        'stage',
        'due_at',
    ];

    protected function casts(): array
    {
        return [
            'question' => 'array',
            'stage' => 'integer',
            'due_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(KidsChild::class, 'child_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(KidsTopic::class, 'topic_id');
    }
}
