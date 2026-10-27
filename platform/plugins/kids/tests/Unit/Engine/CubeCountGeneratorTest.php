<?php

namespace Botble\Kids\Tests\Unit\Engine;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Engine\Generators\CubeCountGenerator;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

class CubeCountGeneratorTest extends TestCase
{
    private CubeCountGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new CubeCountGenerator;
    }

    public function test_key_is_cube_count(): void
    {
        $this->assertSame('cube_count', $this->generator->key());
    }

    public function test_cube_count_without_gaps(): void
    {
        $config = [
            'levels' => [
                '1' => [
                    'rows' => 3,
                    'cols' => 2,
                    'max_height' => 3,
                    'allow_gaps' => false,
                ],
            ],
        ];

        for ($seed = 1; $seed <= 50; $seed++) {
            $rng = new Randomizer(new Mt19937($seed));
            $q = $this->generator->generate(1, $rng, $config);

            $this->assertSame('cube_count', $q->type);
            $this->assertSame('integer', $q->answer->kind);

            $heights = $q->prompt['heights'];
            $this->assertCount(3, $heights);

            $total = 0;
            foreach ($heights as $row) {
                $this->assertCount(2, $row);
                foreach ($row as $cell) {
                    $this->assertGreaterThanOrEqual(1, $cell);
                    $this->assertLessThanOrEqual(3, $cell);
                    $total += $cell;
                }
            }

            $this->assertSame((string) $total, $q->answer->value);
            $this->assertNotEmpty($q->hints);
            $this->assertNotEmpty($q->solution);
            $lastSolution = $q->solution[array_key_last($q->solution)];
            $this->assertStringContainsString((string) $total, $lastSolution);
        }
    }
}
