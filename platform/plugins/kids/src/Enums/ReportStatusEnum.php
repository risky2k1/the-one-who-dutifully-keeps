<?php

namespace Botble\Kids\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static ReportStatusEnum OPEN()
 * @method static ReportStatusEnum RESOLVED()
 */
class ReportStatusEnum extends Enum
{
    public const OPEN = 'open';

    public const RESOLVED = 'resolved';

    public static $langPath = 'plugins/kids::kids.report_statuses';
}
