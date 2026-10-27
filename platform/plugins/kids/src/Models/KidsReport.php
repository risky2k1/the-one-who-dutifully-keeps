<?php

namespace Botble\Kids\Models;

use Botble\Base\Models\BaseModel;
use Botble\Kids\Enums\ReportStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KidsReport extends BaseModel
{
    protected $table = 'kids_reports';

    protected $fillable = [
        'question_id',
        'parent_id',
        'note',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReportStatusEnum::class,
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(KidsSessionQuestion::class, 'question_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(KidsParent::class, 'parent_id');
    }
}
