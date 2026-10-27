<?php

namespace Botble\Kids\Tests\Unit\Engine;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Engine\Hints\ArithmeticHints;
use PHPUnit\Framework\TestCase;

class ArithmeticHintsTest extends TestCase
{
    public function test_hints_for_addition_with_carry(): void
    {
        $result = ArithmeticHints::for('+', 37, 48);

        $this->assertContains('30 + 40 = 70', $result['hints']);
        $this->assertContains('7 + 8 = 15', $result['hints']);
        $this->assertContains('70 + 15 = ?', $result['hints']);

        $lastSolution = end($result['solution']);
        $this->assertSame('37 + 48 = 85', $lastSolution);

        foreach ($result['hints'] as $hint) {
            $this->assertStringNotContainsString('85', $hint);
        }
    }

    public function test_hints_for_subtraction_with_borrow(): void
    {
        $result = ArithmeticHints::for('-', 52, 17);

        $this->assertContains('52 - 10 = 42', $result['hints']);
        $this->assertContains('42 - 7 = ?', $result['hints']);

        $lastSolution = end($result['solution']);
        $this->assertSame('52 - 17 = 35', $lastSolution);

        foreach ($result['hints'] as $hint) {
            $this->assertStringNotContainsString('35', $hint);
        }
    }

    public function test_hints_for_multiplication(): void
    {
        $result = ArithmeticHints::for('×', 5, 7);

        $this->assertContains('5 × 7 = 7 + 7 + 7 + 7 + 7', $result['hints']);

        $lastSolution = end($result['solution']);
        $this->assertSame('5 × 7 = 35', $lastSolution);

        foreach ($result['hints'] as $hint) {
            $this->assertStringNotContainsString('35', $hint);
        }
    }

    public function test_hints_for_division(): void
    {
        $result = ArithmeticHints::for('÷', 14, 2);

        $lastSolution = end($result['solution']);
        $this->assertSame('14 ÷ 2 = 7', $lastSolution);

        foreach ($result['hints'] as $hint) {
            $this->assertStringNotContainsString('7', $hint);
        }
    }
}
