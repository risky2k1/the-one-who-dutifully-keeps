<?php

namespace Botble\Kids\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Kids\Forms\KidsWordTemplateForm;
use Botble\Kids\Http\Requests\KidsWordTemplateRequest;
use Botble\Kids\Models\KidsWordTemplate;
use Botble\Kids\Tables\KidsWordTemplateTable;
use Illuminate\Support\Facades\Auth;

class KidsWordTemplateController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add('Kids Math', route('kids.topics.index'))
            ->add('Word Templates', route('kids.word-templates.index'));
    }

    public function index(KidsWordTemplateTable $table)
    {
        abort_unless(Auth::user()->hasPermission('kids.word-templates.index'), 403);
        $this->pageTitle('Word Problem Templates');

        return $table->renderTable();
    }

    public function create()
    {
        abort_unless(Auth::user()->hasPermission('kids.word-templates.create'), 403);
        $this->pageTitle('Create Word Template');

        return KidsWordTemplateForm::create()->renderForm();
    }

    public function store(KidsWordTemplateRequest $request)
    {
        abort_unless(Auth::user()->hasPermission('kids.word-templates.create'), 403);
        $data = $request->validated();
        if (isset($data['var_ranges']) && is_string($data['var_ranges'])) {
            $data['var_ranges'] = json_decode($data['var_ranges'], true) ?? [];
        }

        $template = KidsWordTemplate::query()->create($data);

        return $this
            ->httpResponse()
            ->setPreviousRoute('kids.word-templates.index')
            ->setNextRoute('kids.word-templates.edit', $template->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(KidsWordTemplate $wordTemplate)
    {
        abort_unless(Auth::user()->hasPermission('kids.word-templates.edit'), 403);
        $this->pageTitle('Edit Word Template #'.$wordTemplate->getKey());

        return KidsWordTemplateForm::createFromModel($wordTemplate)->renderForm();
    }

    public function update(KidsWordTemplate $wordTemplate, KidsWordTemplateRequest $request)
    {
        abort_unless(Auth::user()->hasPermission('kids.word-templates.edit'), 403);
        $data = $request->validated();
        if (isset($data['var_ranges']) && is_string($data['var_ranges'])) {
            $data['var_ranges'] = json_decode($data['var_ranges'], true) ?? [];
        }

        $wordTemplate->update($data);

        return $this
            ->httpResponse()
            ->setPreviousRoute('kids.word-templates.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(KidsWordTemplate $wordTemplate)
    {
        abort_unless(Auth::user()->hasPermission('kids.word-templates.destroy'), 403);

        return DeleteResourceAction::make($wordTemplate);
    }
}
