<?php

namespace Botble\Kids\Forms;

use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Kids\Engine\GeneratorRegistry;
use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Kids\Http\Requests\KidsTopicRequest;
use Botble\Kids\Models\KidsTopic;

class KidsTopicForm extends FormAbstract
{
    public function setup(): void
    {
        /** @var GeneratorRegistry $registry */
        $registry = app(GeneratorRegistry::class);
        $generatorChoices = ['' => 'None (Category/Group)'] + array_combine(
            array_keys($registry->all()),
            array_map(fn ($k) => ucfirst(str_replace('_', ' ', $k)), array_keys($registry->all()))
        );

        $configValue = '';
        if ($this->getModel() && $this->getModel()->exists) {
            $raw = $this->getModel()->generator_config;
            $configValue = is_array($raw) ? json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string) $raw;
        }

        $this
            ->model(KidsTopic::class)
            ->setValidatorClass(KidsTopicRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->label('Topic Name')->required())
            ->add('code', TextField::class, TextFieldOption::make()->label('Unique Code')->required()->placeholder('e.g. g2.add_sub_100'))
            ->add(
                'grade',
                SelectField::class,
                SelectFieldOption::make()
                    ->label('Grade')
                    ->choices([
                        1 => 'Grade 1',
                        2 => 'Grade 2',
                        3 => 'Grade 3',
                        4 => 'Grade 4',
                        5 => 'Grade 5',
                    ])
                    ->required()
            )
            ->add(
                'parent_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label('Parent Topic')
                    ->choices(function () {
                        $modelId = $this->getModel()?->getKey();
                        $topics = KidsTopic::query()->orderBy('grade')->orderBy('name')->get();
                        $choices = [0 => '— None (Root) —'];
                        foreach ($topics as $t) {
                            if ($modelId && $t->getKey() == $modelId) {
                                continue;
                            }
                            $choices[$t->getKey()] = "[Grade {$t->grade}] {$t->name} ({$t->code})";
                        }

                        return $choices;
                    })
                    ->searchable()
            )
            ->add(
                'generator',
                SelectField::class,
                SelectFieldOption::make()
                    ->label('Generator Type')
                    ->choices($generatorChoices)
                    ->helperText('Select a generator if this is a leaf practice topic, or None if it is a category/unit.')
            )
            ->add(
                'generator_config',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label('Generator Config (JSON)')
                    ->value($configValue)
                    ->helperText('JSON parameters passed to the generator (e.g. {"mode": "add_no_carry", "allow_zero": false}).')
            )
            ->add(
                'status',
                SelectField::class,
                StatusFieldOption::make()
                    ->choices(TopicStatusEnum::labels())
            )
            ->setBreakFieldPoint('status');
    }
}
