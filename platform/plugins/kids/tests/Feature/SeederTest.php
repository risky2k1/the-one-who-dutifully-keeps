<?php

namespace Botble\Kids\Tests\Feature;

require_once dirname(__DIR__).'/KidsTestCase.php';

use Botble\Kids\Database\Seeders\KidsTopicSeeder;
use Botble\Kids\Engine\GeneratorRegistry;
use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Kids\Models\KidsTopic;
use Botble\Kids\Models\KidsWordTemplate;
use Botble\Kids\Tests\KidsTestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

class SeederTest extends KidsTestCase
{
    public function test_seeder_is_idempotent_and_seeds_correct_data(): void
    {
        $seeder = new KidsTopicSeeder;

        // Run 1st time
        $seeder->run();
        $topicCount1 = KidsTopic::query()->count();
        $templateCount1 = KidsWordTemplate::query()->count();

        // Run 2nd time
        $seeder->run();
        $topicCount2 = KidsTopic::query()->count();
        $templateCount2 = KidsWordTemplate::query()->count();

        $this->assertSame($topicCount1, $topicCount2, 'Seeder must be idempotent for topics');
        $this->assertSame($templateCount1, $templateCount2, 'Seeder must be idempotent for templates');

        // Check word templates
        $approvedTemplates = KidsWordTemplate::query()
            ->where('status', TopicStatusEnum::APPROVED)
            ->get();
        $this->assertGreaterThanOrEqual(12, $approvedTemplates->count(), 'At least 12 word templates must be approved');

        // Check non-grade 2 topics: none should be approved
        $nonGrade2Approved = KidsTopic::query()
            ->where('grade', '!=', 2)
            ->where('status', TopicStatusEnum::APPROVED)
            ->count();
        $this->assertSame(0, $nonGrade2Approved, 'No topic of grade 1/3/4/5 should be approved');

        // Check Grade 2 leaf topics: all approved and generators work for levels 1..5 across 50 seeds
        /** @var GeneratorRegistry $registry */
        $registry = app(GeneratorRegistry::class);
        $grade2LeafTopics = KidsTopic::query()
            ->where('grade', 2)
            ->whereNotNull('generator')
            ->get();

        $this->assertCount(7, $grade2LeafTopics, 'Grade 2 must have exactly 7 leaf topics');

        foreach ($grade2LeafTopics as $topic) {
            $this->assertSame(TopicStatusEnum::APPROVED, (string) ($topic->status?->getValue() ?? $topic->status));
            $this->assertTrue($registry->has($topic->generator), "Generator {$topic->generator} must be registered");

            $generator = $registry->get($topic->generator);
            $config = $topic->generator_config ?? [];

            for ($level = 1; $level <= 5; $level++) {
                for ($seed = 1; $seed <= 50; $seed++) {
                    $rng = new Randomizer(new Mt19937($seed));
                    $question = $generator->generate($level, $rng, $config);

                    $this->assertNotNull($question, "Topic {$topic->code} failed at level {$level} seed {$seed}");
                    $this->assertNotEmpty($question->prompt, "Prompt cannot be empty for {$topic->code}");
                    $this->assertNotSame('', $question->answer->display(), "Answer cannot be empty for {$topic->code}");
                }
            }
        }
    }
}
