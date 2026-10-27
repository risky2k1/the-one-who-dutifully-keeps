<?php

namespace Botble\Kids\Engine\Generators;

use Botble\Kids\Engine\Answer;
use Botble\Kids\Engine\Question;
use Random\Randomizer;
use RuntimeException;

final class ComparisonGenerator implements Generator
{
    public function key(): string
    {
        return 'comparison';
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function generate(int $level, Randomizer $rng, array $config): Question
    {
        $levelConfig = $config['levels'][$level] ?? $config['levels'][(string) $level] ?? null;
        if (! is_array($levelConfig)) {
            throw new RuntimeException("Missing level configuration for comparison level {$level}");
        }

        $mode = $levelConfig['mode'] ?? 'number';
        $max = $levelConfig['max'] ?? 100;

        return match ($mode) {
            'number' => self::generateNumberComparison($max, $level, $rng),
            'expr_vs_number' => self::generateExprVsNumber($max, $level, $rng),
            'expr_vs_expr' => self::generateExprVsExpr($max, $level, $rng),
            default => throw new RuntimeException("Unknown comparison mode {$mode}"),
        };
    }

    private static function generateNumberComparison(int $max, int $level, Randomizer $rng): Question
    {
        $min = 1;
        $left = $rng->getInt($min, $max);

        // ~1/3 chance of equal
        $isEqual = $rng->getInt(1, 3) === 1;
        $right = $isEqual ? $left : $rng->getInt($min, $max);

        $symbol = $left > $right ? '>' : ($left < $right ? '<' : '=');

        return new Question(
            'comparison',
            [
                'type' => 'comparison',
                'left' => (string) $left,
                'right' => (string) $right,
                'choices' => ['>', '<', '='],
            ],
            new Answer('choice', $symbol),
            ['So sánh chữ số từ hàng cao nhất đến hàng thấp nhất'],
            ["{$left} {$symbol} {$right}"],
            [
                'mode' => 'number',
                'left' => $left,
                'right' => $right,
                'level' => $level,
            ]
        );
    }

    private static function generateExprVsNumber(int $max, int $level, Randomizer $rng): Question
    {
        $op = $rng->getInt(0, 1) === 0 ? '+' : '-';

        if ($op === '+') {
            $half = max(2, intdiv($max, 2));
            $a = $rng->getInt(1, $half);
            $b = $rng->getInt(1, $half);
            $leftVal = $a + $b;
        } else {
            $a = $rng->getInt(5, $max);
            $b = $rng->getInt(1, $a - 1);
            $leftVal = $a - $b;
        }

        $isEqual = $rng->getInt(1, 3) === 1;
        if ($isEqual) {
            $right = $leftVal;
        } else {
            $offset = $rng->getInt(-3, 3);
            if ($offset === 0) {
                $offset = 1;
            }
            $right = max(1, min($max, $leftVal + $offset));
        }

        $symbol = $leftVal > $right ? '>' : ($leftVal < $right ? '<' : '=');
        $leftExpr = "{$a} {$op} {$b}";

        return new Question(
            'comparison',
            [
                'type' => 'comparison',
                'left' => $leftExpr,
                'right' => (string) $right,
                'choices' => ['>', '<', '='],
            ],
            new Answer('choice', $symbol),
            ["Tính vế trái trước: {$leftExpr} = ?"],
            [
                "{$leftExpr} = {$leftVal}",
                "{$leftVal} {$symbol} {$right}",
                "{$leftExpr} {$symbol} {$right}",
            ],
            [
                'mode' => 'expr_vs_number',
                'left' => $leftExpr,
                'right' => $right,
                'level' => $level,
            ]
        );
    }

    private static function generateExprVsExpr(int $max, int $level, Randomizer $rng): Question
    {
        $half = max(2, intdiv($max, 2));
        $a1 = $rng->getInt(1, $half);
        $b1 = $rng->getInt(1, $half);
        $leftVal = $a1 + $b1;
        $leftExpr = "{$a1} + {$b1}";

        $isEqual = $rng->getInt(1, 3) === 1;
        if ($isEqual) {
            $a2 = $rng->getInt(1, max(1, $leftVal - 1));
            $b2 = $leftVal - $a2;
            $rightVal = $leftVal;
        } else {
            $a2 = $rng->getInt(1, $half);
            $b2 = $rng->getInt(1, $half);
            $rightVal = $a2 + $b2;
        }
        $rightExpr = "{$a2} + {$b2}";

        $symbol = $leftVal > $rightVal ? '>' : ($leftVal < $rightVal ? '<' : '=');

        return new Question(
            'comparison',
            [
                'type' => 'comparison',
                'left' => $leftExpr,
                'right' => $rightExpr,
                'choices' => ['>', '<', '='],
            ],
            new Answer('choice', $symbol),
            ["Tính kết quả từng vế trước: {$leftExpr} = ? và {$rightExpr} = ?"],
            [
                "{$leftExpr} = {$leftVal}",
                "{$rightExpr} = {$rightVal}",
                "{$leftVal} {$symbol} {$rightVal}",
                "{$leftExpr} {$symbol} {$rightExpr}",
            ],
            [
                'mode' => 'expr_vs_expr',
                'left' => $leftExpr,
                'right' => $rightExpr,
                'level' => $level,
            ]
        );
    }
}
