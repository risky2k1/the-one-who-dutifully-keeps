<?php

namespace Botble\Kids\Engine;

final class AnswerMatcher
{
    public static function matches(Answer $expected, string $input): bool
    {
        $trimmed = trim($input);
        if ($trimmed === '') {
            return false;
        }

        return match ($expected->kind) {
            'choice' => $trimmed === $expected->value,
            'integer' => self::matchInteger($expected->value, $trimmed),
            'decimal' => self::matchDecimal($expected->value, $trimmed),
            'fraction' => self::matchFraction($expected, $trimmed),
            default => false,
        };
    }

    private static function matchInteger(string $expectedValue, string $input): bool
    {
        $parsed = NumberParser::parse($input, 'integer');
        if ($parsed === null) {
            return false;
        }

        return (int) $parsed === (int) $expectedValue;
    }

    private static function matchDecimal(string $expectedValue, string $input): bool
    {
        $parsed = NumberParser::parse($input, 'decimal');
        if ($parsed === null) {
            return false;
        }

        $expectedNormalized = str_replace(',', '.', $expectedValue);

        return bccomp($expectedNormalized, $parsed, 6) === 0;
    }

    private static function matchFraction(Answer $expected, string $input): bool
    {
        // First check if input contains decimal point or comma -> fractions reject decimals (e.g. "0.5")
        if (str_contains($input, '.') || str_contains($input, ',')) {
            return false;
        }

        // Check if simplest form is required
        if ($expected->requireSimplest) {
            if (preg_match('/^(\d+)\/(\d+)$/', $input, $m)) {
                $n = (int) $m[1];
                $d = (int) $m[2];
                if (self::gcd($n, $d) !== 1) {
                    return false;
                }
            } elseif (preg_match('/^(\d+)\s+(\d+)\/(\d+)$/', $input, $m)) {
                $n = (int) $m[2];
                $d = (int) $m[3];
                if (self::gcd($n, $d) !== 1) {
                    return false;
                }
            } else {
                return false;
            }
        }

        $parsedInput = NumberParser::parse($input, 'fraction');
        if ($parsedInput === null) {
            return false;
        }

        $parsedExpected = NumberParser::parse($expected->value, 'fraction');
        if ($parsedExpected === null) {
            return false;
        }

        [$expNum, $expDen] = array_map('intval', explode('/', $parsedExpected));
        [$inNum, $inDen] = array_map('intval', explode('/', $parsedInput));

        return ($expNum * $inDen) === ($inNum * $expDen);
    }

    private static function gcd(int $a, int $b): int
    {
        $a = abs($a);
        $b = abs($b);

        while ($b !== 0) {
            $t = $b;
            $b = $a % $b;
            $a = $t;
        }

        return $a;
    }
}
