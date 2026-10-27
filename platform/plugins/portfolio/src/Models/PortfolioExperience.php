<?php

namespace Botble\Portfolio\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;

class PortfolioExperience extends BaseModel
{
    protected $table = 'portfolio_experiences';

    protected $fillable = [
        'slug',
        'role',
        'organization',
        'organization_note',
        'period',
        'location',
        'summary',
        'highlights',
        'technologies',
        'order',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'highlights' => 'array',
        'technologies' => 'array',
        'order' => 'integer',
    ];
}

