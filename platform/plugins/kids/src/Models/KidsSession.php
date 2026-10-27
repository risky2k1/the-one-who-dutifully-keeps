<?php

namespace Botble\Kids\Models;

use Botble\Base\Models\BaseModel;
use Botble\Kids\Enums\SessionStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KidsSession extends BaseModel
{
    protected $table = 'kids_sessions';

    protected $fillable = [
        'child_id',
        'assignment_id',
        'seed',
        'status',
        'started_at',
        'expires_at',
        'completed_at',
        'first_try_correct',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'seed' => 'integer',
            'status' => SessionStatusEnum::class,
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
            'first_try_correct' => 'integer',
            'total' => 'integer',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(KidsChild::class, 'child_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(KidsAssignment::class, 'assignment_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(KidsSessionQuestion::class, 'session_id')->orderBy('position');
    }

    public function reward(): HasOne
    {
        return $this->hasOne(KidsReward::class, 'session_id');
    }
}
