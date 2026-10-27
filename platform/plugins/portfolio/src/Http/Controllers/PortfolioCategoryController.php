<?php

namespace Botble\Portfolio\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Portfolio\Forms\PortfolioCategoryForm;
use Botble\Portfolio\Http\Requests\PortfolioCategoryRequest;
use Botble\Portfolio\Models\PortfolioCategory;
use Botble\Portfolio\Tables\PortfolioCategoryTable;

class PortfolioCategoryController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add('Portfolio', route('portfolio.projects.index'))
            ->add('Categories', route('portfolio.categories.index'));
    }

    public function index(PortfolioCategoryTable $table)
    {
        $this->pageTitle('Portfolio Categories');

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle('Create Portfolio Category');

        return PortfolioCategoryForm::create()->renderForm();
    }

    public function store(PortfolioCategoryRequest $request)
    {
        $form = PortfolioCategoryForm::create()->setRequest($request);
        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('portfolio.categories.index')
            ->setNextRoute('portfolio.categories.edit', $form->getModel()->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(PortfolioCategory $category)
    {
        $this->pageTitle('Edit Category: ' . $category->name);

        return PortfolioCategoryForm::createFromModel($category)->renderForm();
    }

    public function update(PortfolioCategory $category, PortfolioCategoryRequest $request)
    {
        PortfolioCategoryForm::createFromModel($category)->setRequest($request)->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('portfolio.categories.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(PortfolioCategory $category)
    {
        return DeleteResourceAction::make($category);
    }
}
