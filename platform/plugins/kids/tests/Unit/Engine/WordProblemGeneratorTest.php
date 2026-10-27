<?php

namespace Botble\Kids\Tests\Unit\Engine;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Engine\Generators\WordProblemGenerator;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

class WordProblemGeneratorTest extends TestCase
{
    private WordProblemGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new WordProblemGenerator;
    }

    public function test_key_is_word_problem(): void
    {
        $this->assertSame('word_problem', $this->generator->key());
    }

    public function test_word_problem_generation(): void
    {
        $config = [
            'templates' => [
                [
                    'id' => 1,
                    'body' => '{name} có {a} {item}, được cho thêm {b} {item}. Hỏi {name} có tất cả bao nhiêu {item}?',
                    'op' => '+',
                    'unit' => 'quả',
                ],
            ],
            'items' => ['quả cam'],
            'names' => ['Nam', 'Mai'],
            'levels' => [
                '1' => [
                    'ops' => ['+'],
                    'a' => [10, 20],
                    'b' => [1, 9],
                    'carry' => false,
                    'result_max' => 30,
                    'missing' => false,
                ],
            ],
        ];

        for ($seed = 1; $seed <= 50; $seed++) {
            $rng = new Randomizer(new Mt19937($seed));
            $q = $this->generator->generate(1, $rng, $config);

            $this->assertSame('word_problem', $q->type);
            $this->assertSame('number', $q->prompt['input']);
            $this->assertSame('quả', $q->prompt['unit']);

            // Body placeholders must all be resolved
            $this->assertStringNotContainsString('{', $q->prompt['text']);
            $this->assertStringNotContainsString('}', $q->prompt['text']);

            $a = $q->params['a'];
            $b = $q->params['b'];
            $this->assertSame((string) ($a + $b), $q->answer->value);

            $this->assertNotEmpty($q->hints);
            $this->assertNotEmpty($q->solution);
            $lastSolution = $q->solution[array_key_last($q->solution)];
            $this->assertStringContainsString((string) ($a + $b), $lastSolution);
        }
    }
}
