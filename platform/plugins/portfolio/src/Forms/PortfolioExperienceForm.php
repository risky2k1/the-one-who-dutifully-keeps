<?php

namespace Botble\Portfolio\Forms;

use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Portfolio\Http\Requests\PortfolioExperienceRequest;
use Botble\Portfolio\Models\PortfolioExperience;

class PortfolioExperienceForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(PortfolioExperience::class)
            ->setValidatorClass(PortfolioExperienceRequest::class)
            ->add('role', TextField::class, TextFieldOption::make()->label('Role / Title')->required())
            ->add('slug', TextField::class, TextFieldOption::make()->label('Slug / ID')->required())
            ->add('organization', TextField::class, TextFieldOption::make()->label('Organization / Company')->required())
            ->add('organization_note', TextField::class, TextFieldOption::make()->label('Organization Note (e.g. Distributed Core Team)'))
            ->add('period', TextField::class, TextFieldOption::make()->label('Period (e.g. 2023 — Present)')->required())
            ->add('location', TextField::class, TextFieldOption::make()->label('Location (e.g. Stockholm / Remote)'))
            ->add('summary', TextareaField::class, TextareaFieldOption::make()->label('Executive Summary')->rows(3))
            ->add('status', SelectField::class, StatusFieldOption::make());
    }
}

