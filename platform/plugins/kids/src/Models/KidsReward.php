<?php

namespace Botble\Kids\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KidsReward extends BaseModel
{
    protected $table = 'kids_rewards';

    protected $fillable = [
        'child_id',
        'session_id',
        'sticker_key',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(KidsChild::class, 'child_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(KidsSession::class, 'session_id');
    }
}
