<?php

namespace Botble\Portfolio\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Contracts\HasTreeCategory as HasTreeCategoryContract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Base\Traits\HasTreeCategory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioCategory extends BaseModel implements HasTreeCategoryContract
{
    use HasTreeCategory;

    protected $table = 'portfolio_categories';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'badge',
        'accent_color',
        'description',
        'order',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
        'description' => SafeContent::class,
        'order' => 'integer',
        'parent_id' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PortfolioCategory::class, 'parent_id')->withDefault();
    }

    public function children(): HasMany
    {
        return $this->hasMany(PortfolioCategory::class, 'parent_id')->orderBy('order', 'asc');
    }

    public function activeChildren(): HasMany
    {
        return $this
            ->children()
            ->where('status', BaseStatusEnum::PUBLISHED)
            ->with(['activeChildren']);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            PortfolioProject::class,
            'portfolio_project_categories',
            'category_id',
            'project_id'
        );
    }

    public function skills(): HasMany
    {
        return $this->hasMany(PortfolioSkill::class, 'category_id')->orderBy('order', 'asc');
    }
}
