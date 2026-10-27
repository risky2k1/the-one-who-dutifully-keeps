<?php

namespace Botble\Kids\Tests\Unit\Engine;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Engine\DifficultyAdjuster;
use PHPUnit\Framework\TestCase;

class DifficultyAdjusterTest extends TestCase
{
    public function test_increases_difficulty_when_at_least_four_correct(): void
    {
        // 4 correct, 1 wrong -> level 2 to 3
        $this->assertSame(3, DifficultyAdjuster::next(2, [true, true, true, true, false]));
        // 5 correct -> level 2 to 3
        $this->assertSame(3, DifficultyAdjuster::next(2, [true, true, true, true, true]));
    }

    public function test_decreases_difficulty_when_at_least_three_wrong(): void
    {
        // 3 wrong, 2 correct -> level 2 to 1
        $this->assertSame(1, DifficultyAdjuster::next(2, [false, false, false, true, true]));
        // 4 wrong -> level 3 to 2
        $this->assertSame(2, DifficultyAdjuster::next(3, [false, false, false, false, true]));
    }

    public function test_clamps_within_one_to_five(): void
    {
        // level 5 cannot go above 5
        $this->assertSame(5, DifficultyAdjuster::next(5, [true, true, true, true, true]));
        // level 1 cannot go below 1
        $this->assertSame(1, DifficultyAdjuster::next(1, [false, false, false, false, false]));
    }

    public function test_keeps_level_when_fewer_than_five_results(): void
    {
        $this->assertSame(3, DifficultyAdjuster::next(3, [true, true, true, true]));
        $this->assertSame(2, DifficultyAdjuster::next(2, []));
    }

    public function test_keeps_level_when_mixed_results(): void
    {
        // 3 correct, 2 wrong -> neither >= 4 correct nor >= 3 wrong
        $this->assertSame(3, DifficultyAdjuster::next(3, [true, true, false, false, true]));
    }
}
