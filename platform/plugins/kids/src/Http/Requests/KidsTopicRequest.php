<?php

namespace Botble\Kids\Http\Requests;

use Botble\Kids\Engine\GeneratorRegistry;
use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Random\Engine\Mt19937;
use Random\Randomizer;

class KidsTopicRequest extends Request
{
    public function rules(): array
    {
        /** @var GeneratorRegistry $registry */
        $registry = app(GeneratorRegistry::class);
        $generatorKeys = array_keys($registry->all());

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', Rule::unique('kids_topics', 'code')->ignore($this->route('topic'))],
            'grade' => ['required', 'integer', 'between:1,5'],
            'parent_id' => ['nullable', 'integer'],
            'generator' => ['nullable', 'string', Rule::in($generatorKeys)],
            'generator_config' => ['nullable'],
            'status' => [Rule::in(TopicStatusEnum::values())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $generatorKey = $this->input('generator');
            $rawConfig = $this->input('generator_config');

            if (! $generatorKey) {
                return;
            }

            $config = [];
            if (! empty($rawConfig)) {
                if (is_string($rawConfig)) {
                    $decoded = json_decode($rawConfig, true);
                    if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                        $validator->errors()->add('generator_config', 'Invalid JSON syntax for generator config.');

                        return;
                    }
                    $config = $decoded;
                } elseif (is_array($rawConfig)) {
                    $config = $rawConfig;
                }
            }

            /** @var GeneratorRegistry $registry */
            $registry = app(GeneratorRegistry::class);
            if (! $registry->has($generatorKey)) {
                $validator->errors()->add('generator', "Unknown generator '{$generatorKey}'.");

                return;
            }

            $generator = $registry->get($generatorKey);

            for ($lvl = 1; $lvl <= 5; $lvl++) {
                for ($seed = 1; $seed <= 20; $seed++) {
                    try {
                        $rng = new Randomizer(new Mt19937($seed));
                        $question = $generator->generate($lvl, $rng, $config);
                        if (! $question) {
                            $validator->errors()->add('generator_config', "Generator at level {$lvl} with seed {$seed} produced null.");

                            return;
                        }
                    } catch (\Throwable $e) {
                        $validator->errors()->add('generator_config', "Generator failed at level {$lvl}: {$e->getMessage()}");

                        return;
                    }
                }
            }
        });
    }
}
