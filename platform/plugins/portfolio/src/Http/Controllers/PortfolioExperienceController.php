<?php

namespace Botble\Portfolio\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Portfolio\Forms\PortfolioExperienceForm;
use Botble\Portfolio\Http\Requests\PortfolioExperienceRequest;
use Botble\Portfolio\Models\PortfolioExperience;
use Botble\Portfolio\Tables\PortfolioExperienceTable;

class PortfolioExperienceController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add('Portfolio', route('portfolio.experiences.index'))
            ->add('Experiences', route('portfolio.experiences.index'));
    }

    public function index(PortfolioExperienceTable $table)
    {
        $this->pageTitle('Portfolio Career Progression & Experiences');

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle('Create Experience Record');

        return PortfolioExperienceForm::create()->renderForm();
    }

    public function store(PortfolioExperienceRequest $request)
    {
        $form = PortfolioExperienceForm::create()->setRequest($request);
        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('portfolio.experiences.index')
            ->setNextRoute('portfolio.experiences.edit', $form->getModel()->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(PortfolioExperience $experience)
    {
        $this->pageTitle('Edit Experience: ' . $experience->role . ' at ' . $experience->organization);

        return PortfolioExperienceForm::createFromModel($experience)->renderForm();
    }

    public function update(PortfolioExperience $experience, PortfolioExperienceRequest $request)
    {
        PortfolioExperienceForm::createFromModel($experience)->setRequest($request)->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('portfolio.experiences.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(PortfolioExperience $experience)
    {
        return DeleteResourceAction::make($experience);
    }
}

