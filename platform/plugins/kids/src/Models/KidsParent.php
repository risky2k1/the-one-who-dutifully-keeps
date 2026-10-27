<?php

namespace Botble\Kids\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class KidsParent extends BaseModel implements AuthenticatableContract
{
    use Authenticatable;
    use HasApiTokens;

    protected $table = 'kids_parents';

    protected $fillable = [
        'name',
        'username',
        'password',
        'pin_hash',
        'status',
    ];

    protected $hidden = [
        'password',
        'pin_hash',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function children(): HasMany
    {
        return $this->hasMany(KidsChild::class, 'parent_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(KidsAssignment::class, 'parent_id');
    }
}
