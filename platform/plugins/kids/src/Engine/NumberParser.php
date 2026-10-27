<?php

namespace Botble\Kids\Engine;

final class NumberParser
{
    public static function parse(string $input, string $kind): ?string
    {
        $clean = trim($input);
        if ($clean === '') {
            return null;
        }

        return match ($kind) {
            'integer' => self::parseInteger($clean),
            'decimal' => self::parseDecimal($clean),
            'fraction' => self::parseFraction($clean),
            'choice' => $clean,
            default => null,
        };
    }

    private static function parseInteger(string $input): ?string
    {
        if (preg_match('/^-?\d+$/', $input)) {
            return $input;
        }

        // Thousands separated by dot: 1.000, 12.345.678
        if (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $input)) {
            return str_replace('.', '', $input);
        }

        // Thousands separated by space: 1 000, 12 345 678
        if (preg_match('/^-?\d{1,3}( \d{3})+$/', $input)) {
            return str_replace(' ', '', $input);
        }

        return null;
    }

    private static function parseDecimal(string $input): ?string
    {
        $normalized = str_replace(',', '.', $input);

        if (preg_match('/^-?\d+(\.\d+)?$/', $normalized)) {
            return $normalized;
        }

        return null;
    }

    private static function parseFraction(string $input): ?string
    {
        // Mixed number: e.g. "1 1/2" -> 3/2
        if (preg_match('/^(\d+)\s+(\d+)\/(\d+)$/', $input, $matches)) {
            $whole = (int) $matches[1];
            $num = (int) $matches[2];
            $den = (int) $matches[3];

            if ($den === 0) {
                return null;
            }

            $totalNum = ($whole * $den) + $num;

            return "{$totalNum}/{$den}";
        }

        // Simple fraction: e.g. "2/4", "1/2"
        if (preg_match('/^(\d+)\/(\d+)$/', $input, $matches)) {
            $num = (int) $matches[1];
            $den = (int) $matches[2];

            if ($den === 0) {
                return null;
            }

            return "{$num}/{$den}";
        }

        return null;
    }
}
