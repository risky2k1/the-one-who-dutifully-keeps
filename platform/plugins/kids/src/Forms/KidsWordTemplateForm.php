<?php

namespace Botble\Kids\Forms;

use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Kids\Enums\TemplateStatusEnum;
use Botble\Kids\Http\Requests\KidsWordTemplateRequest;
use Botble\Kids\Models\KidsTopic;
use Botble\Kids\Models\KidsWordTemplate;

class KidsWordTemplateForm extends FormAbstract
{
    public function setup(): void
    {
        $rangesValue = '';
        if ($this->getModel() && $this->getModel()->exists) {
            $raw = $this->getModel()->var_ranges;
            $rangesValue = is_array($raw) ? json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string) $raw;
        }

        $this
            ->model(KidsWordTemplate::class)
            ->setValidatorClass(KidsWordTemplateRequest::class)
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
                'operation',
                SelectField::class,
                SelectFieldOption::make()
                    ->label('Operation')
                    ->choices([
                        'add' => 'Addition (+)',
                        'sub' => 'Subtraction (-)',
                        'mul' => 'Multiplication (*)',
                        'div' => 'Division (/)',
                    ])
                    ->required()
            )
            ->add(
                'topic_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label('Associated Topic')
                    ->choices(function () {
                        $topics = KidsTopic::query()->orderBy('grade')->orderBy('name')->get();
                        $choices = [0 => '— None (General for Grade) —'];
                        foreach ($topics as $t) {
                            $choices[$t->getKey()] = "[Grade {$t->grade}] {$t->name}";
                        }

                        return $choices;
                    })
                    ->searchable()
            )
            ->add(
                'template',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label('Problem Template Text')
                    ->required()
                    ->helperText('Use placeholders like {a}, {b}. Example: "Bình có {a} viên bi, An có {b} viên bi. Hỏi cả hai bạn có bao nhiêu viên bi?"')
            )
            ->add(
                'var_ranges',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label('Variable Ranges (JSON)')
                    ->required()
                    ->value($rangesValue)
                    ->helperText('JSON object with min and max bounds for each variable. Example: {"a": [10, 50], "b": [5, 20]}')
            )
            ->add(
                'result_variable',
                TextField::class,
                TextFieldOption::make()
                    ->label('Result Variable Name')
                    ->required()
                    ->defaultValue('ans')
                    ->placeholder('e.g. ans')
            )
            ->add(
                'unit',
                TextField::class,
                TextFieldOption::make()
                    ->label('Unit Label')
                    ->placeholder('e.g. viên bi, quả cam, bông hoa')
            )
            ->add(
                'status',
                SelectField::class,
                StatusFieldOption::make()
                    ->choices(TemplateStatusEnum::labels())
            )
            ->setBreakFieldPoint('status');
    }
}
