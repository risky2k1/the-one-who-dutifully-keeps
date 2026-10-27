<?php

namespace Botble\Kids\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static TopicStatusEnum DRAFT()
 * @method static TopicStatusEnum APPROVED()
 */
class TopicStatusEnum extends Enum
{
    public const DRAFT = 'draft';

    public const APPROVED = 'approved';

    public static $langPath = 'plugins/kids::kids.statuses';
}
