<?php

namespace Botble\Portfolio\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PortfolioProject extends BaseModel
{
    protected $table = 'portfolio_projects';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'short_summary',
        'overview',
        'role',
        'timeline',
        'accent_color',
        'secondary_color',
        'architecture_diagram_type',
        'challenges',
        'outcomes',
        'tech_stack',
        'metrics',
        'links',
        'order',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'challenges' => 'array',
        'outcomes' => 'array',
        'tech_stack' => 'array',
        'metrics' => 'array',
        'links' => 'array',
        'order' => 'integer',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            PortfolioCategory::class,
            'portfolio_project_categories',
            'project_id',
            'category_id'
        );
    }

    protected function primaryCategory(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->categories->first()?->name ?? $this->category ?? 'Backend API'
        );
    }
}

