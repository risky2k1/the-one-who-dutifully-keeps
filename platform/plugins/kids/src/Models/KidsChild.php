<?php

namespace Botble\Kids\Models;

use Botble\Base\Models\BaseModel;
use Botble\Kids\Enums\TextbookEnum;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class KidsChild extends BaseModel implements AuthenticatableContract
{
    use Authenticatable;
    use HasApiTokens;

    protected $table = 'kids_children';

    protected $fillable = [
        'parent_id',
        'name',
        'avatar',
        'grade',
        'textbook',
        'disabled_topic_ids',
        'assigned_first',
        'streak_current',
        'streak_best',
        'last_practiced_on',
    ];

    protected function casts(): array
    {
        return [
            'grade' => 'integer',
            'textbook' => TextbookEnum::class,
            'disabled_topic_ids' => 'array',
            'assigned_first' => 'boolean',
            'streak_current' => 'integer',
            'streak_best' => 'integer',
            'last_practiced_on' => 'date',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(KidsParent::class, 'parent_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(KidsSession::class, 'child_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(KidsAssignment::class, 'child_id');
    }

    public function skillLevels(): HasMany
    {
        return $this->hasMany(KidsSkillLevel::class, 'child_id');
    }

    public function reviewItems(): HasMany
    {
        return $this->hasMany(KidsReviewItem::class, 'child_id');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(KidsReward::class, 'child_id');
    }

    public function scopeForParent(Builder $query, KidsParent $parent): Builder
    {
        return $query->where('parent_id', $parent->getKey());
    }
}
