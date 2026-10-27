<?php

namespace Botble\Portfolio\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioSkillGroup extends BaseModel
{
    protected $table = 'portfolio_skill_groups';

    protected $fillable = [
        'name',
        'slug',
        'badge',
        'accent_color',
        'summary',
        'order',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'order' => 'integer',
    ];

    public function skills(): HasMany
    {
        return $this->hasMany(PortfolioSkill::class, 'skill_group_id')->orderBy('order', 'asc');
    }
}

