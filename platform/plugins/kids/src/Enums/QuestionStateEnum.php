<?php

namespace Botble\Kids\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static QuestionStateEnum OPEN()
 * @method static QuestionStateEnum CORRECT()
 * @method static QuestionStateEnum REVEALED()
 */
class QuestionStateEnum extends Enum
{
    public const OPEN = 'open';

    public const CORRECT = 'correct';

    public const REVEALED = 'revealed';

    public static $langPath = 'plugins/kids::kids.question_states';
}
