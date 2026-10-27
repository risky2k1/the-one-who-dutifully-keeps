<?php

namespace Botble\Portfolio\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioSkill extends BaseModel
{
    protected $table = 'portfolio_skills';

    protected $fillable = [
        'skill_group_id',
        'category_id',
        'name',
        'level',
        'context',
        'highlight',
        'order',
    ];

    protected $casts = [
        'highlight' => 'boolean',
        'order' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(PortfolioSkillGroup::class, 'skill_group_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PortfolioCategory::class, 'category_id');
    }
}

