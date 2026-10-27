<?php

namespace Botble\Kids\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KidsSkillLevel extends BaseModel
{
    protected $table = 'kids_skill_levels';

    protected $fillable = [
        'child_id',
        'topic_id',
        'level',
        'level_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'level_changed_at' => 'datetime',
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
