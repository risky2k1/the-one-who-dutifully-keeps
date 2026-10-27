<?php

namespace Botble\Kids\Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Kids\Models\KidsTopic;
use Botble\Kids\Models\KidsWordTemplate;

class KidsTopicSeeder extends BaseSeeder
{
    public function run(): void
    {
        $grade2Data = require dirname(__DIR__, 3).'/database/seeders/data/grade2.php';
        $skeletonData = require dirname(__DIR__, 3).'/database/seeders/data/skeleton.php';

        $codeToId = [];

        // 1. Seed Grade 2 Categories first
        foreach ($grade2Data['categories'] as $cat) {
            $parentId = $cat['parent_code'] ? ($codeToId[$cat['parent_code']] ?? null) : null;
            $topic = KidsTopic::query()->updateOrCreate(
                ['code' => $cat['code']],
                [
                    'name' => $cat['name'],
                    'grade' => $cat['grade'],
                    'parent_id' => $parentId,
                    'generator' => $cat['generator'],
                    'generator_config' => $cat['generator_config'],
                    'status' => $cat['status'],
                ]
            );
            $codeToId[$cat['code']] = $topic->getKey();
        }

        // 2. Seed Grade 2 Topics
        foreach ($grade2Data['topics'] as $t) {
            $parentId = $t['parent_code'] ? ($codeToId[$t['parent_code']] ?? null) : null;
            $topic = KidsTopic::query()->updateOrCreate(
                ['code' => $t['code']],
                [
                    'name' => $t['name'],
                    'grade' => $t['grade'],
                    'parent_id' => $parentId,
                    'generator' => $t['generator'],
                    'generator_config' => $t['generator_config'],
                    'status' => $t['status'],
                ]
            );
            $codeToId[$t['code']] = $topic->getKey();
        }

        // 3. Seed Skeleton topics (Grades 1, 3, 4, 5)
        foreach ($skeletonData as $skel) {
            $parentId = $skel['parent_code'] ? ($codeToId[$skel['parent_code']] ?? null) : null;
            $topic = KidsTopic::query()->updateOrCreate(
                ['code' => $skel['code']],
                [
                    'name' => $skel['name'],
                    'grade' => $skel['grade'],
                    'parent_id' => $parentId,
                    'generator' => $skel['generator'],
                    'generator_config' => $skel['generator_config'],
                    'status' => $skel['status'],
                ]
            );
            $codeToId[$skel['code']] = $topic->getKey();
        }

        // 4. Seed Word Templates (Grade 2)
        $wordTopicId = $codeToId['g2.word_one_step'] ?? null;
        foreach ($grade2Data['word_templates'] as $wt) {
            KidsWordTemplate::query()->updateOrCreate(
                ['body' => $wt['body']],
                [
                    'topic_id' => $wordTopicId,
                    'op' => $wt['op'],
                    'unit' => $wt['unit'],
                    'status' => $wt['status'],
                ]
            );
        }
    }
}
