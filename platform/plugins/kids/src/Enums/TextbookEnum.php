<?php

namespace Botble\Kids\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static TextbookEnum KET_NOI()
 * @method static TextbookEnum CHAN_TROI()
 * @method static TextbookEnum CANH_DIEU()
 */
class TextbookEnum extends Enum
{
    public const KET_NOI = 'ket_noi';

    public const CHAN_TROI = 'chan_troi';

    public const CANH_DIEU = 'canh_dieu';

    public static $langPath = 'plugins/kids::kids.textbooks';
}
