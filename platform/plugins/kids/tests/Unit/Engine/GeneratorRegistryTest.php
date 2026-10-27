<?php

namespace Botble\Kids\Tests\Unit\Engine;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Engine\GeneratorRegistry;
use Botble\Kids\Engine\Generators\ArithmeticGenerator;
use Botble\Kids\Engine\Generators\ComparisonGenerator;
use Botble\Kids\Engine\Generators\CubeCountGenerator;
use Botble\Kids\Engine\Generators\WordProblemGenerator;
use Botble\Kids\Tests\KidsTestCase;

class GeneratorRegistryTest extends KidsTestCase
{
    public function test_registry_contains_all_core_generators(): void
    {
        /** @var GeneratorRegistry $registry */
        $registry = $this->app->make(GeneratorRegistry::class);

        $this->assertInstanceOf(ArithmeticGenerator::class, $registry->get('arithmetic'));
        $this->assertInstanceOf(ComparisonGenerator::class, $registry->get('comparison'));
        $this->assertInstanceOf(CubeCountGenerator::class, $registry->get('cube_count'));
        $this->assertInstanceOf(WordProblemGenerator::class, $registry->get('word_problem'));

        $this->assertTrue($registry->has('arithmetic'));
        $this->assertFalse($registry->has('non_existent'));
        $this->assertCount(4, $registry->all());
    }
}
