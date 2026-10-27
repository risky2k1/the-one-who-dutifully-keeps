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
use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Kids\Http\Requests\KidsWordTemplateRequest;
use Botble\Kids\Models\KidsTopic;
use Botble\Kids\Models\KidsWordTemplate;

class KidsWordTemplateForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(KidsWordTemplate::class)
            ->setValidatorClass(KidsWordTemplateRequest::class)
            ->add(
                'topic_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label('Word Problem Topic')
                    ->choices(function () {
                        $topics = KidsTopic::query()
                            ->where('generator', 'word_problem')
                            ->orderBy('grade')
                            ->orderBy('name')
                            ->get();
                        $choices = [];
                        foreach ($topics as $t) {
                            $choices[$t->getKey()] = "[Grade {$t->grade}] {$t->name}";
                        }

                        return $choices;
                    })
                    ->required()
                    ->searchable()
            )
            ->add(
                'op',
                SelectField::class,
                SelectFieldOption::make()
                    ->label('Operation')
                    ->choices([
                        '+' => 'Addition (+)',
                        '-' => 'Subtraction (-)',
                        '×' => 'Multiplication (×)',
                        '÷' => 'Division (÷)',
                    ])
                    ->required()
            )
            ->add(
                'body',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label('Problem Body Template')
                    ->required()
                    ->helperText('Placeholders: {name}, {a}, {b}, {item}. Example: "{name} có {a} {item}, bạn cho thêm {b} {item}. Hỏi có tất cả bao nhiêu {item}?"')
            )
            ->add(
                'unit',
                TextField::class,
                TextFieldOption::make()
                    ->label('Unit')
                    ->required()
                    ->placeholder('e.g. quả cam, cái kẹo, quyển vở')
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
