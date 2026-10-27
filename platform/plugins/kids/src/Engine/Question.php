<?php

namespace Botble\Kids\Engine;

final class Question
{
    /**
     * @param  array<string, mixed>  $prompt
     * @param  string[]  $hints
     * @param  string[]  $solution
     * @param  array<string, mixed>  $params
     */
    public function __construct(
        public readonly string $type,
        public readonly array $prompt,
        public readonly Answer $answer,
        public readonly array $hints,
        public readonly array $solution,
        public readonly array $params = [],
    ) {}
}
