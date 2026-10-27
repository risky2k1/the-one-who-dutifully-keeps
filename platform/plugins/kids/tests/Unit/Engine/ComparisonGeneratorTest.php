<?php

namespace Botble\Kids\Tests\Unit\Engine;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Engine\Generators\ComparisonGenerator;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

class ComparisonGeneratorTest extends TestCase
{
    private ComparisonGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new ComparisonGenerator;
    }

    public function test_key_is_comparison(): void
    {
        $this->assertSame('comparison', $this->generator->key());
    }

    public function test_comparison_number_mode(): void
    {
        $config = [
            'levels' => [
                '1' => [
                    'mode' => 'number',
                    'max' => 20,
                ],
            ],
        ];

        $equalCount = 0;

        for ($seed = 1; $seed <= 100; $seed++) {
            $rng = new Randomizer(new Mt19937($seed));
            $q = $this->generator->generate(1, $rng, $config);

            $this->assertSame('comparison', $q->type);
            $this->assertContains($q->answer->value, ['>', '<', '=']);
            $this->assertSame('choice', $q->answer->kind);

            $left = (int) $q->prompt['left'];
            $right = (int) $q->prompt['right'];

            $this->assertLessThanOrEqual(20, $left);
            $this->assertLessThanOrEqual(20, $right);

            if ($left > $right) {
                $this->assertSame('>', $q->answer->value);
            } elseif ($left < $right) {
                $this->assertSame('<', $q->answer->value);
            } else {
                $this->assertSame('=', $q->answer->value);
                $equalCount++;
            }

            $this->assertNotEmpty($q->hints);
            $this->assertNotEmpty($q->solution);
            $lastSolution = $q->solution[array_key_last($q->solution)];
            $this->assertStringContainsString($q->answer->value, $lastSolution);
        }

        // About 1/3 of the questions should have "=" answer
        $this->assertGreaterThan(15, $equalCount);
    }

    public function test_comparison_expr_vs_number_mode(): void
    {
        $config = [
            'levels' => [
                '1' => [
                    'mode' => 'expr_vs_number',
                    'max' => 100,
                ],
            ],
        ];

        for ($seed = 1; $seed <= 50; $seed++) {
            $rng = new Randomizer(new Mt19937($seed));
            $q = $this->generator->generate(1, $rng, $config);

            $this->assertMatchesRegularExpression('/^\d+ [+\-] \d+$/', $q->prompt['left']);
            $this->assertMatchesRegularExpression('/^\d+$/', $q->prompt['right']);
        }
    }
}
