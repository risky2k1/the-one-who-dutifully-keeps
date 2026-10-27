<?php

namespace Botble\Portfolio\Forms;

use Botble\Base\Forms\FieldOptions\ColorFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\ColorField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Portfolio\Http\Requests\PortfolioSkillGroupRequest;
use Botble\Portfolio\Models\PortfolioSkillGroup;

class PortfolioSkillGroupForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(PortfolioSkillGroup::class)
            ->setValidatorClass(PortfolioSkillGroupRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->label('Skill Group Title')->required())
            ->add('badge', TextField::class, TextFieldOption::make()->label('Filter Pill Badge (e.g. Infrastructure, Services)')->required())
            ->add(
                'accent_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label('Accent Color')
                    ->defaultValue('#5ec2b7')
            )
            ->add('summary', TextareaField::class, TextareaFieldOption::make()->label('Domain Summary')->rows(3))
            ->add('status', SelectField::class, StatusFieldOption::make())
            ->setBreakFieldPoint('status');
    }
}

