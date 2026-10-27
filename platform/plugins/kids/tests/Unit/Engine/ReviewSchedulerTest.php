<?php

namespace Botble\Kids\Tests\Unit\Engine;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Engine\ReviewScheduler;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ReviewSchedulerTest extends TestCase
{
    private DateTimeImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->now = new DateTimeImmutable('2026-10-27 10:00:00');
    }

    public function test_new_question_correct_does_not_schedule(): void
    {
        $res = ReviewScheduler::afterAnswer(null, true, $this->now);

        $this->assertNull($res['stage']);
        $this->assertNull($res['due_at']);
    }

    public function test_new_question_wrong_schedules_stage_one_in_one_day(): void
    {
        $res = ReviewScheduler::afterAnswer(null, false, $this->now);

        $this->assertSame(1, $res['stage']);
        $this->assertNotNull($res['due_at']);
        $this->assertSame('2026-10-28 10:00:00', $res['due_at']->format('Y-m-d H:i:s'));
    }

    public function test_stage_one_correct_schedules_stage_two_in_three_days(): void
    {
        $res = ReviewScheduler::afterAnswer(1, true, $this->now);

        $this->assertSame(2, $res['stage']);
        $this->assertNotNull($res['due_at']);
        $this->assertSame('2026-10-30 10:00:00', $res['due_at']->format('Y-m-d H:i:s'));
    }

    public function test_stage_one_wrong_reschedules_stage_one_in_one_day(): void
    {
        $res = ReviewScheduler::afterAnswer(1, false, $this->now);

        $this->assertSame(1, $res['stage']);
        $this->assertNotNull($res['due_at']);
        $this->assertSame('2026-10-28 10:00:00', $res['due_at']->format('Y-m-d H:i:s'));
    }

    public function test_stage_two_correct_completes_review(): void
    {
        $res = ReviewScheduler::afterAnswer(2, true, $this->now);

        $this->assertNull($res['stage']);
        $this->assertNull($res['due_at']);
    }

    public function test_stage_two_wrong_reschedules_stage_one_in_one_day(): void
    {
        $res = ReviewScheduler::afterAnswer(2, false, $this->now);

        $this->assertSame(1, $res['stage']);
        $this->assertNotNull($res['due_at']);
        $this->assertSame('2026-10-28 10:00:00', $res['due_at']->format('Y-m-d H:i:s'));
    }
}
