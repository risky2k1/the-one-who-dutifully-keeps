<?php

namespace Botble\Portfolio\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Portfolio\Forms\PortfolioSkillGroupForm;
use Botble\Portfolio\Http\Requests\PortfolioSkillGroupRequest;
use Botble\Portfolio\Models\PortfolioSkillGroup;
use Botble\Portfolio\Tables\PortfolioSkillGroupTable;

class PortfolioSkillGroupController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add('Portfolio', route('portfolio.skills.index'))
            ->add('Skills', route('portfolio.skills.index'));
    }

    public function index(PortfolioSkillGroupTable $table)
    {
        $this->pageTitle('Portfolio Skill Domains & Groups');

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle('Create Skill Domain');

        return PortfolioSkillGroupForm::create()->renderForm();
    }

    public function store(PortfolioSkillGroupRequest $request)
    {
        $form = PortfolioSkillGroupForm::create()->setRequest($request);
        $form->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('portfolio.skills.index')
            ->setNextRoute('portfolio.skills.edit', $form->getModel()->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(PortfolioSkillGroup $skill)
    {
        $this->pageTitle('Edit Skill Domain: ' . $skill->name);

        return PortfolioSkillGroupForm::createFromModel($skill)->renderForm();
    }

    public function update(PortfolioSkillGroup $skill, PortfolioSkillGroupRequest $request)
    {
        PortfolioSkillGroupForm::createFromModel($skill)->setRequest($request)->save();

        return $this
            ->httpResponse()
            ->setPreviousRoute('portfolio.skills.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(PortfolioSkillGroup $skill)
    {
        return DeleteResourceAction::make($skill);
    }
}

