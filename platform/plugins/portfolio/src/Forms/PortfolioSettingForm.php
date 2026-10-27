<?php

namespace Botble\Portfolio\Forms;

use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Portfolio\Http\Requests\PortfolioSettingRequest;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Forms\SettingForm;

class PortfolioSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle('Portfolio & 3D Showcase Settings')
            ->setSectionDescription('Configure the persona, hero introduction, about philosophy, and contact settings rendered in the 3D portfolio.')
            ->setValidatorClass(PortfolioSettingRequest::class)
            ->add(
                'portfolio_persona_name',
                TextField::class,
                TextFieldOption::make()
                    ->label('Persona Full Name')
                    ->value(Setting::get('portfolio_persona_name', 'Sora Lindqvist (Demo Persona)'))
            )
            ->add(
                'portfolio_persona_role',
                TextField::class,
                TextFieldOption::make()
                    ->label('Engineering Role / Job Title')
                    ->value(Setting::get('portfolio_persona_role', 'Staff Platform & Cloud Infrastructure Engineer'))
            )
            ->add(
                'portfolio_persona_specialization',
                TextField::class,
                TextFieldOption::make()
                    ->label('Specialization Subtitle')
                    ->value(Setting::get('portfolio_persona_specialization', 'Distributed Backend APIs • Kubernetes Fabric • GitOps CI/CD • OpenTelemetry'))
            )
            ->add(
                'portfolio_persona_location',
                TextField::class,
                TextFieldOption::make()
                    ->label('Location')
                    ->value(Setting::get('portfolio_persona_location', 'Stockholm / Remote (Sample Profile)'))
            )
            ->add(
                'portfolio_persona_availability_badge',
                TextField::class,
                TextFieldOption::make()
                    ->label('Availability Badge')
                    ->value(Setting::get('portfolio_persona_availability_badge', 'Demo Showcase • Interactive 3D World'))
            )
            ->add(
                'portfolio_intro_headline',
                TextField::class,
                TextFieldOption::make()
                    ->label('Intro Headline')
                    ->value(Setting::get('portfolio_intro_headline', 'Architecting calm, self-healing cloud platforms & low-latency API meshes.'))
            )
            ->add(
                'portfolio_intro_lead',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label('Intro Lead Paragraph')
                    ->rows(3)
                    ->value(Setting::get('portfolio_intro_lead', 'Drive the coral studio coupe along the continuous pastel highway—either by selecting a destination in the top dock or clicking directly on any of the six sculptural landmarks in the 3D world.'))
            )
            ->add(
                'portfolio_about_headline',
                TextField::class,
                TextFieldOption::make()
                    ->label('About Section Headline')
                    ->value(Setting::get('portfolio_about_headline', 'Infrastructure should feel invisible when it works—and unmistakable in its clarity when inspected.'))
            )
            ->add(
                'portfolio_contact_headline',
                TextField::class,
                TextFieldOption::make()
                    ->label('Contact Headline')
                    ->value(Setting::get('portfolio_contact_headline', 'Ready to customize or connect?'))
            )
            ->add(
                'portfolio_contact_subtitle',
                TextareaField::class,
                TextareaFieldOption::make()
                    ->label('Contact Subtitle')
                    ->rows(2)
                    ->value(Setting::get('portfolio_contact_subtitle', 'All endpoints below are clearly marked demonstration placeholders configured in the CMS or you can send a message directly using the contact form.'))
            );
    }
}

