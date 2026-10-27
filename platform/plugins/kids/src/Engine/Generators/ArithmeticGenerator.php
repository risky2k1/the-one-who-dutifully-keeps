<?php

namespace Botble\Kids\Engine\Generators;

use Botble\Kids\Engine\Answer;
use Botble\Kids\Engine\Hints\ArithmeticHints;
use Botble\Kids\Engine\Question;
use Random\Randomizer;
use RuntimeException;

final class ArithmeticGenerator implements Generator
{
    public function key(): string
    {
        return 'arithmetic';
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function generate(int $level, Randomizer $rng, array $config): Question
    {
        $levelConfig = $config['levels'][$level] ?? $config['levels'][(string) $level] ?? null;
        if (! is_array($levelConfig)) {
            throw new RuntimeException("Missing level configuration for level {$level}");
        }

        $ops = $levelConfig['ops'] ?? ['+'];
        $rangeA = $levelConfig['a'] ?? [1, 50];
        $rangeB = $levelConfig['b'] ?? [1, 50];
        $carry = $levelConfig['carry'] ?? null;
        $resultMax = $levelConfig['result_max'] ?? null;
        $missing = (bool) ($levelConfig['missing'] ?? false);

        for ($attempt = 0; $attempt < 1000; $attempt++) {
            $opIndex = $rng->getInt(0, count($ops) - 1);
            $op = $ops[$opIndex];

            if ($op === '÷' || $op === '/') {
                $b = $rng->getInt($rangeA[0], $rangeA[1]);
                $quot = $rng->getInt($rangeB[0], $rangeB[1]);
                $a = $b * $quot;

                if ($resultMax !== null && ($a > $resultMax || $quot > $resultMax)) {
                    continue;
                }

                $expectedAnswer = $missing ? $b : $quot;
                $promptText = $missing ? "{$a} ÷ ? = {$quot}" : "{$a} ÷ {$b} = ?";
                $hintsData = ArithmeticHints::for('÷', $a, $b);

                return new Question(
                    'arithmetic',
                    [
                        'type' => 'arithmetic',
                        'text' => $promptText,
                        'input' => 'number',
                    ],
                    new Answer('integer', (string) $expectedAnswer),
                    $hintsData['hints'],
                    $hintsData['solution'],
                    [
                        'op' => '÷',
                        'a' => $a,
                        'b' => $b,
                        'missing' => $missing,
                        'level' => $level,
                    ]
                );
            }

            $a = $rng->getInt($rangeA[0], $rangeA[1]);
            $b = $rng->getInt($rangeB[0], $rangeB[1]);

            if ($op === '+') {
                $res = $a + $b;
                if ($resultMax !== null && $res > $resultMax) {
                    continue;
                }

                $unitsCarry = (($a % 10) + ($b % 10)) >= 10;
                $tensCarry = (((int) ($a / 10) % 10) + ((int) ($b / 10) % 10) + ($unitsCarry ? 1 : 0)) >= 10;
                $hasCarry = $unitsCarry || $tensCarry;

                if ($carry === true && ! $hasCarry) {
                    continue;
                }
                if ($carry === false && $hasCarry) {
                    continue;
                }

                $expectedAnswer = $missing ? $b : $res;
                $promptText = $missing ? "{$a} + ? = {$res}" : "{$a} + {$b} = ?";
                $hintsData = ArithmeticHints::for('+', $a, $b);

                return new Question(
                    'arithmetic',
                    [
                        'type' => 'arithmetic',
                        'text' => $promptText,
                        'input' => 'number',
                    ],
                    new Answer('integer', (string) $expectedAnswer),
                    $hintsData['hints'],
                    $hintsData['solution'],
                    [
                        'op' => '+',
                        'a' => $a,
                        'b' => $b,
                        'missing' => $missing,
                        'level' => $level,
                    ]
                );
            }

            if ($op === '-') {
                if ($a < $b) {
                    continue;
                }

                $res = $a - $b;
                if ($resultMax !== null && $res > $resultMax) {
                    continue;
                }

                $hasBorrow = ($a % 10) < ($b % 10);

                if ($carry === true && ! $hasBorrow) {
                    continue;
                }
                if ($carry === false && $hasBorrow) {
                    continue;
                }

                $expectedAnswer = $missing ? $b : $res;
                $promptText = $missing ? "{$a} - ? = {$res}" : "{$a} - {$b} = ?";
                $hintsData = ArithmeticHints::for('-', $a, $b);

                return new Question(
                    'arithmetic',
                    [
                        'type' => 'arithmetic',
                        'text' => $promptText,
                        'input' => 'number',
                    ],
                    new Answer('integer', (string) $expectedAnswer),
                    $hintsData['hints'],
                    $hintsData['solution'],
                    [
                        'op' => '-',
                        'a' => $a,
                        'b' => $b,
                        'missing' => $missing,
                        'level' => $level,
                    ]
                );
            }

            if ($op === '×' || $op === '*') {
                $res = $a * $b;
                if ($resultMax !== null && $res > $resultMax) {
                    continue;
                }

                $expectedAnswer = $missing ? $b : $res;
                $promptText = $missing ? "{$a} × ? = {$res}" : "{$a} × {$b} = ?";
                $hintsData = ArithmeticHints::for('×', $a, $b);

                return new Question(
                    'arithmetic',
                    [
                        'type' => 'arithmetic',
                        'text' => $promptText,
                        'input' => 'number',
                    ],
                    new Answer('integer', (string) $expectedAnswer),
                    $hintsData['hints'],
                    $hintsData['solution'],
                    [
                        'op' => '×',
                        'a' => $a,
                        'b' => $b,
                        'missing' => $missing,
                        'level' => $level,
                    ]
                );
            }
        }

        throw new RuntimeException("Cannot generate arithmetic question for level {$level} within 1000 attempts");
    }
}
