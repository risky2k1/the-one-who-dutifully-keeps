<?php

namespace Botble\Kids\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static RecurrenceEnum NONE()
 * @method static RecurrenceEnum DAILY()
 */
class RecurrenceEnum extends Enum
{
    public const NONE = 'none';

    public const DAILY = 'daily';

    public static $langPath = 'plugins/kids::kids.recurrences';
}
