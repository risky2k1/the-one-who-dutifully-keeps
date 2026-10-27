<?php

namespace Botble\Kids\Engine\Generators;

use Botble\Kids\Engine\Question;
use Random\Randomizer;

interface Generator
{
    public function key(): string;

    /**
     * @param  array<string, mixed>  $config
     */
    public function generate(int $level, Randomizer $rng, array $config): Question;
}
