<?php

namespace Botble\Kids\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static AssignmentDifficultyEnum ADAPTIVE()
 * @method static AssignmentDifficultyEnum EASY()
 * @method static AssignmentDifficultyEnum MEDIUM()
 * @method static AssignmentDifficultyEnum HARD()
 */
class AssignmentDifficultyEnum extends Enum
{
    public const ADAPTIVE = 'adaptive';

    public const EASY = 'easy';

    public const MEDIUM = 'medium';

    public const HARD = 'hard';

    public static $langPath = 'plugins/kids::kids.difficulties';
}
