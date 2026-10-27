<?php

namespace Botble\Kids\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Kids\Forms\KidsTopicForm;
use Botble\Kids\Http\Requests\KidsTopicRequest;
use Botble\Kids\Models\KidsTopic;
use Botble\Kids\Tables\KidsTopicTable;
use Illuminate\Support\Facades\Auth;

class KidsTopicController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add('Kids Math', route('kids.topics.index'))
            ->add('Topics', route('kids.topics.index'));
    }

    public function index(KidsTopicTable $table)
    {
        abort_unless(Auth::user()->hasPermission('kids.topics.index'), 403);
        $this->pageTitle('Kids Topics');

        return $table->renderTable();
    }

    public function create()
    {
        abort_unless(Auth::user()->hasPermission('kids.topics.create'), 403);
        $this->pageTitle('Create Kids Topic');

        return KidsTopicForm::create()->renderForm();
    }

    public function store(KidsTopicRequest $request)
    {
        abort_unless(Auth::user()->hasPermission('kids.topics.create'), 403);
        $data = $request->validated();
        if (isset($data['generator_config']) && is_string($data['generator_config'])) {
            $data['generator_config'] = json_decode($data['generator_config'], true) ?? [];
        }

        $topic = KidsTopic::query()->create($data);

        return $this
            ->httpResponse()
            ->setPreviousRoute('kids.topics.index')
            ->setNextRoute('kids.topics.edit', $topic->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(KidsTopic $topic)
    {
        abort_unless(Auth::user()->hasPermission('kids.topics.edit'), 403);
        $this->pageTitle('Edit Topic: '.$topic->name);

        return KidsTopicForm::createFromModel($topic)->renderForm();
    }

    public function update(KidsTopic $topic, KidsTopicRequest $request)
    {
        abort_unless(Auth::user()->hasPermission('kids.topics.edit'), 403);
        $data = $request->validated();
        if (isset($data['generator_config']) && is_string($data['generator_config'])) {
            $data['generator_config'] = json_decode($data['generator_config'], true) ?? [];
        }

        $topic->update($data);

        return $this
            ->httpResponse()
            ->setPreviousRoute('kids.topics.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(KidsTopic $topic)
    {
        abort_unless(Auth::user()->hasPermission('kids.topics.destroy'), 403);

        return DeleteResourceAction::make($topic);
    }
}
