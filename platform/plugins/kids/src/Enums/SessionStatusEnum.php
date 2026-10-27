<?php

namespace Botble\Kids\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static SessionStatusEnum IN_PROGRESS()
 * @method static SessionStatusEnum COMPLETED()
 * @method static SessionStatusEnum EXPIRED()
 */
class SessionStatusEnum extends Enum
{
    public const IN_PROGRESS = 'in_progress';

    public const COMPLETED = 'completed';

    public const EXPIRED = 'expired';

    public static $langPath = 'plugins/kids::kids.session_statuses';
}
