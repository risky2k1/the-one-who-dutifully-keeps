<?php

namespace Botble\Portfolio\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Portfolio\Forms\PortfolioProjectForm;
use Botble\Portfolio\Http\Requests\PortfolioProjectRequest;
use Botble\Portfolio\Models\PortfolioProject;
use Botble\Portfolio\Tables\PortfolioProjectTable;

class PortfolioProjectController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add('Portfolio', route('portfolio.projects.index'))
            ->add('Projects', route('portfolio.projects.index'));
    }

    public function index(PortfolioProjectTable $table)
    {
        $this->pageTitle('Portfolio Architecture Projects');

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle('Create Project');

        return PortfolioProjectForm::create()->renderForm();
    }

    public function store(PortfolioProjectRequest $request)
    {
        $form = PortfolioProjectForm::create()->setRequest($request);
        $form->save();

        /** @var PortfolioProject $project */
        $project = $form->getModel();
        if ($request->has('categories')) {
            $project->categories()->sync((array) $request->input('categories', []));
            if (! $request->filled('category') && $first = $project->categories()->first()) {
                $project->update(['category' => $first->name]);
            }
        }

        return $this
            ->httpResponse()
            ->setPreviousRoute('portfolio.projects.index')
            ->setNextRoute('portfolio.projects.edit', $project->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(PortfolioProject $project)
    {
        $this->pageTitle('Edit Project: ' . $project->name);

        return PortfolioProjectForm::createFromModel($project)->renderForm();
    }

    public function update(PortfolioProject $project, PortfolioProjectRequest $request)
    {
        PortfolioProjectForm::createFromModel($project)->setRequest($request)->save();

        if ($request->has('categories')) {
            $project->categories()->sync((array) $request->input('categories', []));
            if (! $request->filled('category') && $first = $project->categories()->first()) {
                $project->update(['category' => $first->name]);
            }
        }

        return $this
            ->httpResponse()
            ->setPreviousRoute('portfolio.projects.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(PortfolioProject $project)
    {
        return DeleteResourceAction::make($project);
    }
}

