<?php

namespace Botble\Kids\Engine\Hints;

final class ArithmeticHints
{
    /**
     * @return array{hints: string[], solution: string[]}
     */
    public static function for(string $op, int $a, int $b): array
    {
        return match ($op) {
            '+' => self::forAddition($a, $b),
            '-' => self::forSubtraction($a, $b),
            '×', '*' => self::forMultiplication($a, $b),
            '÷', '/' => self::forDivision($a, $b),
            default => [
                'hints' => ['Thực hiện phép tính theo thứ tự'],
                'solution' => ["Kết quả là {$a}"],
            ],
        };
    }

    /**
     * @return array{hints: string[], solution: string[]}
     */
    private static function forAddition(int $a, int $b): array
    {
        $sum = $a + $b;

        if ($a >= 10 && $b >= 10) {
            $aTens = (int) ($a / 10) * 10;
            $bTens = (int) ($b / 10) * 10;
            $aUnits = $a % 10;
            $bUnits = $b % 10;

            $sumTens = $aTens + $bTens;
            $sumUnits = $aUnits + $bUnits;

            return [
                'hints' => [
                    "{$aTens} + {$bTens} = {$sumTens}",
                    "{$aUnits} + {$bUnits} = {$sumUnits}",
                    "{$sumTens} + {$sumUnits} = ?",
                ],
                'solution' => [
                    "{$aTens} + {$bTens} = {$sumTens}",
                    "{$aUnits} + {$bUnits} = {$sumUnits}",
                    "{$sumTens} + {$sumUnits} = {$sum}",
                    "{$a} + {$b} = {$sum}",
                ],
            ];
        }

        if ($a >= 10 || $b >= 10) {
            $large = max($a, $b);
            $small = min($a, $b);
            $tens = (int) ($large / 10) * 10;
            $units = $large % 10;
            $sumUnits = $units + $small;

            return [
                'hints' => [
                    "{$units} + {$small} = {$sumUnits}",
                    "{$tens} + {$sumUnits} = ?",
                ],
                'solution' => [
                    "{$units} + {$small} = {$sumUnits}",
                    "{$tens} + {$sumUnits} = {$sum}",
                    "{$a} + {$b} = {$sum}",
                ],
            ];
        }

        return [
            'hints' => ["Đếm thêm {$b} bắt đầu từ {$a}"],
            'solution' => ["{$a} + {$b} = {$sum}"],
        ];
    }

    /**
     * @return array{hints: string[], solution: string[]}
     */
    private static function forSubtraction(int $a, int $b): array
    {
        $diff = $a - $b;

        if ($b >= 10) {
            $bTens = (int) ($b / 10) * 10;
            $bUnits = $b % 10;
            $step1 = $a - $bTens;

            return [
                'hints' => [
                    "{$a} - {$bTens} = {$step1}",
                    "{$step1} - {$bUnits} = ?",
                ],
                'solution' => [
                    "{$a} - {$bTens} = {$step1}",
                    "{$step1} - {$bUnits} = {$diff}",
                    "{$a} - {$b} = {$diff}",
                ],
            ];
        }

        if ($a >= 10 && ($a % 10) < $b) {
            $aUnits = $a % 10;
            $step1 = $a - $aUnits;
            $remB = $b - $aUnits;

            return [
                'hints' => [
                    "{$a} - {$aUnits} = {$step1}",
                    "{$step1} - {$remB} = ?",
                ],
                'solution' => [
                    "{$a} - {$aUnits} = {$step1}",
                    "{$step1} - {$remB} = {$diff}",
                    "{$a} - {$b} = {$diff}",
                ],
            ];
        }

        return [
            'hints' => ["Đếm bớt {$b} bắt đầu từ {$a}"],
            'solution' => ["{$a} - {$b} = {$diff}"],
        ];
    }

    /**
     * @return array{hints: string[], solution: string[]}
     */
    private static function forMultiplication(int $a, int $b): array
    {
        $prod = $a * $b;
        $repeated = implode(' + ', array_fill(0, $a, (string) $b));

        return [
            'hints' => ["{$a} × {$b} = {$repeated}"],
            'solution' => [
                "{$a} × {$b} = {$repeated}",
                "{$a} × {$b} = {$prod}",
            ],
        ];
    }

    /**
     * @return array{hints: string[], solution: string[]}
     */
    private static function forDivision(int $a, int $b): array
    {
        $quot = $b !== 0 ? intdiv($a, $b) : 0;

        return [
            'hints' => ["Nhẩm lại bảng nhân: {$b} × ? = {$a}"],
            'solution' => [
                "{$b} × {$quot} = {$a}",
                "{$a} ÷ {$b} = {$quot}",
            ],
        ];
    }
}
