<?php

namespace Botble\Kids\Engine;

final class DifficultyAdjuster
{
    /**
     * @param  bool[]  $firstTryResults  Array of booleans, newest first, since last level change
     */
    public static function next(int $level, array $firstTryResults): int
    {
        if (count($firstTryResults) < 5) {
            return $level;
        }

        $slice = array_slice($firstTryResults, 0, 5);
        $correctCount = count(array_filter($slice, fn (bool $val): bool => $val === true));
        $wrongCount = count($slice) - $correctCount;

        if ($correctCount >= 4) {
            return min(5, $level + 1);
        }

        if ($wrongCount >= 3) {
            return max(1, $level - 1);
        }

        return $level;
    }
}
