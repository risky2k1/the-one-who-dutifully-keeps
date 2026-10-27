<?php

namespace Botble\Kids\Tests\Unit\Engine;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Engine\Generators\ArithmeticGenerator;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

class ArithmeticGeneratorTest extends TestCase
{
    private ArithmeticGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new ArithmeticGenerator;
    }

    public function test_key_is_arithmetic(): void
    {
        $this->assertSame('arithmetic', $this->generator->key());
    }

    public function test_addition_without_carry(): void
    {
        $config = [
            'levels' => [
                '1' => [
                    'ops' => ['+'],
                    'a' => [10, 50],
                    'b' => [1, 40],
                    'carry' => false,
                    'result_max' => 100,
                    'missing' => false,
                ],
            ],
        ];

        for ($seed = 1; $seed <= 200; $seed++) {
            $rng = new Randomizer(new Mt19937($seed));
            $question = $this->generator->generate(1, $rng, $config);

            $this->assertSame('arithmetic', $question->type);
            $this->assertSame('number', $question->prompt['input']);
            $this->assertSame('integer', $question->answer->kind);

            $a = $question->params['a'];
            $b = $question->params['b'];
            $sum = $a + $b;

            $this->assertSame((string) $sum, $question->answer->value);
            $this->assertLessThan(10, ($a % 10) + ($b % 10), "Units carry found in seed $seed: $a + $b");
            $this->assertLessThanOrEqual(100, $sum);
        }
    }

    public function test_addition_with_carry(): void
    {
        $config = [
            'levels' => [
                '1' => [
                    'ops' => ['+'],
                    'a' => [15, 59],
                    'b' => [5, 39],
                    'carry' => true,
                    'result_max' => 100,
                    'missing' => false,
                ],
            ],
        ];

        for ($seed = 1; $seed <= 200; $seed++) {
            $rng = new Randomizer(new Mt19937($seed));
            $question = $this->generator->generate(1, $rng, $config);

            $a = $question->params['a'];
            $b = $question->params['b'];

            $hasCarry = (($a % 10) + ($b % 10) >= 10) ||
                (((int) ($a / 10) % 10) + ((int) ($b / 10) % 10) + (($a % 10) + ($b % 10) >= 10 ? 1 : 0) >= 10);

            $this->assertTrue($hasCarry, "Expected carry in seed $seed: $a + $b");
        }
    }

    public function test_subtraction_with_borrow(): void
    {
        $config = [
            'levels' => [
                '1' => [
                    'ops' => ['-'],
                    'a' => [20, 80],
                    'b' => [5, 40],
                    'carry' => true,
                    'result_max' => 100,
                    'missing' => false,
                ],
            ],
        ];

        for ($seed = 1; $seed <= 200; $seed++) {
            $rng = new Randomizer(new Mt19937($seed));
            $question = $this->generator->generate(1, $rng, $config);

            $a = $question->params['a'];
            $b = $question->params['b'];

            $this->assertGreaterThanOrEqual(0, $a - $b);
            $this->assertLessThan($b % 10, $a % 10, "Expected units borrow in seed $seed: $a - $b");
            $this->assertSame((string) ($a - $b), $question->answer->value);
        }
    }

    public function test_division_is_exact(): void
    {
        $config = [
            'levels' => [
                '1' => [
                    'ops' => ['÷'],
                    'a' => [2, 5], // b table (divisor)
                    'b' => [1, 9], // quotient range
                    'carry' => null,
                    'result_max' => 50,
                    'missing' => false,
                ],
            ],
        ];

        for ($seed = 1; $seed <= 200; $seed++) {
            $rng = new Randomizer(new Mt19937($seed));
            $question = $this->generator->generate(1, $rng, $config);

            $a = $question->params['a']; // dividend
            $b = $question->params['b']; // divisor

            $this->assertSame(0, $a % $b, "Dividend must be divisible by divisor: $a / $b");
            $this->assertSame((string) (intdiv($a, $b)), $question->answer->value);
        }
    }

    public function test_missing_number_format(): void
    {
        $config = [
            'levels' => [
                '1' => [
                    'ops' => ['×'],
                    'a' => [2, 5],
                    'b' => [2, 9],
                    'carry' => null,
                    'result_max' => 50,
                    'missing' => true,
                ],
            ],
        ];

        for ($seed = 1; $seed <= 200; $seed++) {
            $rng = new Randomizer(new Mt19937($seed));
            $question = $this->generator->generate(1, $rng, $config);

            $this->assertMatchesRegularExpression('/^\d+ × \? = \d+$/', $question->prompt['text']);
            $this->assertSame((string) $question->params['b'], $question->answer->value);
        }
    }

    public function test_deterministic_with_same_seed(): void
    {
        $config = [
            'levels' => [
                '1' => [
                    'ops' => ['+'],
                    'a' => [10, 50],
                    'b' => [1, 40],
                    'carry' => false,
                    'result_max' => 100,
                    'missing' => false,
                ],
            ],
        ];

        $rng1 = new Randomizer(new Mt19937(42));
        $q1 = $this->generator->generate(1, $rng1, $config);

        $rng2 = new Randomizer(new Mt19937(42));
        $q2 = $this->generator->generate(1, $rng2, $config);

        $this->assertSame($q1->params, $q2->params);
        $this->assertSame($q1->prompt['text'], $q2->prompt['text']);
        $this->assertSame($q1->answer->value, $q2->answer->value);
    }
}
