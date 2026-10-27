<?php

namespace Botble\Kids\Engine\Generators;

use Botble\Kids\Engine\Answer;
use Botble\Kids\Engine\Hints\ArithmeticHints;
use Botble\Kids\Engine\Question;
use Random\Randomizer;
use RuntimeException;

final class WordProblemGenerator implements Generator
{
    private ArithmeticGenerator $arithmeticGenerator;

    public function __construct(?ArithmeticGenerator $arithmeticGenerator = null)
    {
        $this->arithmeticGenerator = $arithmeticGenerator ?? new ArithmeticGenerator;
    }

    public function key(): string
    {
        return 'word_problem';
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function generate(int $level, Randomizer $rng, array $config): Question
    {
        $templates = $config['templates'] ?? [];
        if (empty($templates)) {
            throw new RuntimeException("No word problem templates provided for level {$level}");
        }

        $names = $config['names'] ?? ['An', 'Bình', 'Chi', 'Dũng', 'Hà', 'Linh', 'Mai', 'Nam', 'Phong', 'Vy'];
        $items = $config['items'] ?? ['quả cam', 'quả táo', 'viên bi', 'bông hoa', 'quyển vở', 'cái kẹo'];

        $levelConfig = $config['levels'][$level] ?? $config['levels'][(string) $level] ?? [];
        $allowedOps = $levelConfig['ops'] ?? null;
        if (! empty($allowedOps) && is_array($allowedOps)) {
            $filtered = array_values(array_filter($templates, fn ($t) => in_array($t['op'] ?? '+', $allowedOps, true)));
            if (! empty($filtered)) {
                $templates = $filtered;
            }
        }

        // Pick a template randomly
        $templateIndex = $rng->getInt(0, count($templates) - 1);
        $template = $templates[$templateIndex];

        $op = $template['op'] ?? '+';
        $unit = $template['unit'] ?? 'cái';

        // Set ops for arithmetic generator to match the template's op
        $arithmeticConfig = $config;
        $levelConfig['ops'] = [$op];
        $levelConfig['missing'] = false;
        $arithmeticConfig['levels'] = [$level => $levelConfig];

        $arithmeticQuestion = $this->arithmeticGenerator->generate($level, $rng, $arithmeticConfig);

        $a = $arithmeticQuestion->params['a'];
        $b = $arithmeticQuestion->params['b'];
        $res = $arithmeticQuestion->answer->value;

        $name = $names[$rng->getInt(0, count($names) - 1)];
        $item = $items[$rng->getInt(0, count($items) - 1)];

        $text = str_replace(
            ['{name}', '{a}', '{b}', '{item}'],
            [$name, (string) $a, (string) $b, $item],
            $template['body']
        );

        $opName = match ($op) {
            '+' => 'cộng',
            '-' => 'trừ',
            '×', '*' => 'nhân',
            '÷', '/' => 'chia',
            default => 'tính toán',
        };

        $arithmeticHints = ArithmeticHints::for($op, $a, $b);
        $hints = array_merge(["Bài này dùng phép {$opName}"], $arithmeticHints['hints']);
        $solution = array_merge($arithmeticHints['solution'], ["Đáp số: {$res} {$unit}"]);

        return new Question(
            'word_problem',
            [
                'type' => 'word_problem',
                'text' => $text,
                'unit' => $unit,
                'input' => 'number',
            ],
            new Answer('integer', $res),
            $hints,
            $solution,
            [
                'template_id' => $template['id'] ?? null,
                'op' => $op,
                'a' => $a,
                'b' => $b,
                'unit' => $unit,
                'level' => $level,
            ]
        );
    }
}
