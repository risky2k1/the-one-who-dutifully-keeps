<?php

namespace Botble\Portfolio\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FieldOptions\ColorFieldOption;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\ColorField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Portfolio\Http\Requests\PortfolioCategoryRequest;
use Botble\Portfolio\Models\PortfolioCategory;

class PortfolioCategoryForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(PortfolioCategory::class)
            ->setValidatorClass(PortfolioCategoryRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->label('Category Title')->required())
            ->add(
                'parent_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('core/base::forms.parent'))
                    ->choices(function () {
                        $modelId = null;

                        if ($this->getModel() && $this->getModel()->exists) {
                            $modelId = $this->getModel()->getKey();
                        }

                        $allCategories = PortfolioCategory::query()
                            ->where('status', BaseStatusEnum::PUBLISHED)
                            ->orderBy('order', 'asc')
                            ->orderBy('name', 'asc')
                            ->get();

                        $sorted = sort_item_with_children($allCategories);

                        $categories = [];
                        foreach ($sorted as $category) {
                            if ($modelId && ($modelId === $category->id || $modelId === $category->parent_id)) {
                                continue;
                            }

                            $indent = str_repeat('—— ', (int) ($category->depth ?? 0));
                            $badge = $category->badge ? " [{$category->badge}]" : '';
                            $categories[$category->id] = $indent . $category->name . $badge;
                        }

                        return [0 => trans('core/base::forms.none')] + $categories;
                    })
                    ->searchable()
            )
            ->add(
                'badge',
                TextField::class,
                TextFieldOption::make()->label('Filter Pill Badge (e.g. Infrastructure, Services, SRE)')
            )
            ->add(
                'accent_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label('Accent Pastel Color')
                    ->defaultValue('#5ec2b7')
            )
            ->add(
                'description',
                TextareaField::class,
                DescriptionFieldOption::make()->label('Domain / Category Summary')
            )
            ->add('status', SelectField::class, StatusFieldOption::make())
            ->setBreakFieldPoint('status');
    }
}
