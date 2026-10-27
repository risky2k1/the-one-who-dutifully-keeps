<?php

namespace Botble\Kids\Engine\Generators;

use Botble\Kids\Engine\Answer;
use Botble\Kids\Engine\Question;
use Random\Randomizer;
use RuntimeException;

final class CubeCountGenerator implements Generator
{
    public function key(): string
    {
        return 'cube_count';
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function generate(int $level, Randomizer $rng, array $config): Question
    {
        $levelConfig = $config['levels'][$level] ?? $config['levels'][(string) $level] ?? null;
        if (! is_array($levelConfig)) {
            throw new RuntimeException("Missing level configuration for cube_count level {$level}");
        }

        $rows = (int) ($levelConfig['rows'] ?? 3);
        $cols = (int) ($levelConfig['cols'] ?? 3);
        $maxHeight = (int) ($levelConfig['max_height'] ?? 2);
        $allowGaps = (bool) ($levelConfig['allow_gaps'] ?? false);

        $minH = $allowGaps ? 0 : 1;

        $heights = [];
        $total = 0;
        $allCells = [];

        for ($r = 0; $r < $rows; $r++) {
            $row = [];
            for ($c = 0; $c < $cols; $c++) {
                $h = $rng->getInt($minH, $maxHeight);
                $row[] = $h;
                $total += $h;
                if ($h > 0) {
                    $allCells[] = $h;
                }
            }
            $heights[] = $row;
        }

        if ($total === 0) {
            $heights[0][0] = 1;
            $total = 1;
            $allCells[] = 1;
        }

        $solutionStep = count($allCells) > 1
            ? 'Tổng số khối là: '.implode(' + ', $allCells)." = {$total}"
            : "Tổng số khối là: {$total}";

        return new Question(
            'cube_count',
            [
                'type' => 'cube_count',
                'heights' => $heights,
            ],
            new Answer('integer', (string) $total),
            ['Đếm từng cột rồi cộng lại'],
            [$solutionStep],
            [
                'rows' => $rows,
                'cols' => $cols,
                'max_height' => $maxHeight,
                'allow_gaps' => $allowGaps,
                'level' => $level,
            ]
        );
    }
}
