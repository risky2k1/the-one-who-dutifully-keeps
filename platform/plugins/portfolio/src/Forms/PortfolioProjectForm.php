<?php

namespace Botble\Portfolio\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FieldOptions\ColorFieldOption;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextareaFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\ColorField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\Fields\TreeCategoryField;
use Botble\Base\Forms\FormAbstract;
use Botble\Portfolio\Http\Requests\PortfolioProjectRequest;
use Botble\Portfolio\Models\PortfolioCategory;
use Botble\Portfolio\Models\PortfolioProject;

class PortfolioProjectForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(PortfolioProject::class)
            ->setValidatorClass(PortfolioProjectRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->label('Project Title')->required())
            ->add(
                'categories[]',
                TreeCategoryField::class,
                SelectFieldOption::make()
                    ->label('Categories')
                    ->choices(function () {
                        return PortfolioCategory::query()
                            ->where('status', BaseStatusEnum::PUBLISHED)
                            ->select(['id', 'name', 'parent_id'])
                            ->with('activeChildren')
                            ->where(function ($q) {
                                $q->whereNull('parent_id')->orWhere('parent_id', 0);
                            })
                            ->orderBy('order', 'asc')
                            ->get();
                    })
                    ->when($this->getModel()->exists, function (SelectFieldOption $fieldOption) {
                        return $fieldOption->selected(
                            $this->getModel()->categories()->pluck('portfolio_categories.id')->all()
                        );
                    }, function (SelectFieldOption $fieldOption) {
                        return $fieldOption->selected([]);
                    })
            )
            ->add('role', TextField::class, TextFieldOption::make()->label('Role'))
            ->add('timeline', TextField::class, TextFieldOption::make()->label('Timeline'))
            ->add(
                'accent_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label('Accent Pastel Color')
                    ->defaultValue('#5ec2b7')
            )
            ->add(
                'secondary_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label('Secondary Pastel Color')
                    ->defaultValue('#a8d8ea')
            )
            ->add(
                'architecture_diagram_type',
                SelectField::class,
                SelectFieldOption::make()
                    ->label('Architecture Diagram Archetype')
                    ->choices([
                        'gateway_mesh' => 'AetherGate: Distributed Mesh Topology',
                        'multi_region_k8s' => 'Stratos: Multi-Region Fabric',
                        'gitops_pipeline' => 'PulseForge: Ephemeral GitOps Pipeline',
                    ])
            )
            ->add('short_summary', TextareaField::class, DescriptionFieldOption::make()->label('Short Summary (Card preview)')->rows(3))
            ->add('overview', TextareaField::class, TextareaFieldOption::make()->label('Comprehensive Architectural Overview')->rows(6))
            ->add('status', SelectField::class, StatusFieldOption::make())
            ->setBreakFieldPoint('status');
    }
}
