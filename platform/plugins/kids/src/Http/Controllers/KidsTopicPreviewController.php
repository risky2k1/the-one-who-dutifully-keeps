<?php

namespace Botble\Kids\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Kids\Engine\GeneratorRegistry;
use Botble\Kids\Models\KidsTopic;
use Illuminate\Support\Facades\Auth;
use Random\Engine\Mt19937;
use Random\Randomizer;

class KidsTopicPreviewController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add('Kids Math', route('kids.topics.index'))
            ->add('Topics', route('kids.topics.index'))
            ->add('Preview');
    }

    public function show(KidsTopic $topic, GeneratorRegistry $registry)
    {
        abort_unless(Auth::user()->hasPermission('kids.topics.index'), 403);
        $this->pageTitle('Preview Topic: '.$topic->name);

        $levels = [];
        $totalQuestions = 0;

        if ($topic->generator && $registry->has($topic->generator)) {
            $generator = $registry->get($topic->generator);
            $config = $topic->generator_config ?? [];

            for ($lvl = 1; $lvl <= 5; $lvl++) {
                $questions = [];
                for ($seed = 1; $seed <= 20; $seed++) {
                    $rng = new Randomizer(new Mt19937($seed));
                    $q = $generator->generate($lvl, $rng, $config);
                    $questions[] = $q;
                    $totalQuestions++;
                }
                $levels[$lvl] = $questions;
            }
        }

        return view('plugins/kids::topics.preview', compact('topic', 'levels', 'totalQuestions'));
    }
}
