<?php

namespace Botble\Kids\Engine;

use Botble\Kids\Engine\Generators\Generator;
use InvalidArgumentException;

final class GeneratorRegistry
{
    /**
     * @var array<string, Generator>
     */
    private array $generators = [];

    public function register(Generator $generator): self
    {
        $this->generators[$generator->key()] = $generator;

        return $this;
    }

    public function get(string $key): Generator
    {
        if (! isset($this->generators[$key])) {
            throw new InvalidArgumentException("Generator not found for key: '{$key}'");
        }

        return $this->generators[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->generators[$key]);
    }

    /**
     * @return array<string, Generator>
     */
    public function all(): array
    {
        return $this->generators;
    }
}
