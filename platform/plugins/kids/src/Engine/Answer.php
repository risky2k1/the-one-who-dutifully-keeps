<?php

namespace Botble\Kids\Engine;

final class Answer
{
    public function __construct(
        public readonly string $kind,
        public readonly string $value,
        public readonly bool $requireSimplest = false,
    ) {}

    public function display(): string
    {
        if ($this->kind === 'decimal') {
            return str_replace('.', ',', $this->value);
        }

        return $this->value;
    }
}
