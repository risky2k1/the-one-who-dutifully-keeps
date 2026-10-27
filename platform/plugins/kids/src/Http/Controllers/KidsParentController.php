<?php

namespace Botble\Kids\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Kids\Forms\KidsParentForm;
use Botble\Kids\Http\Requests\KidsParentRequest;
use Botble\Kids\Models\KidsParent;
use Botble\Kids\Tables\KidsParentTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class KidsParentController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add('Kids Math', route('kids.topics.index'))
            ->add('Parents', route('kids.parents.index'));
    }

    public function index(KidsParentTable $table)
    {
        abort_unless(Auth::user()->hasPermission('kids.parents.index'), 403);
        $this->pageTitle('Parent Accounts');

        return $table->renderTable();
    }

    public function create()
    {
        abort_unless(Auth::user()->hasPermission('kids.parents.create'), 403);
        $this->pageTitle('Create Parent Account');

        return KidsParentForm::create()->renderForm();
    }

    public function store(KidsParentRequest $request)
    {
        abort_unless(Auth::user()->hasPermission('kids.parents.create'), 403);
        $data = $request->validated();
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $parent = KidsParent::query()->create($data);

        return $this
            ->httpResponse()
            ->setPreviousRoute('kids.parents.index')
            ->setNextRoute('kids.parents.edit', $parent->getKey())
            ->withCreatedSuccessMessage();
    }

    public function edit(KidsParent $parent)
    {
        abort_unless(Auth::user()->hasPermission('kids.parents.edit'), 403);
        $this->pageTitle('Edit Parent: '.$parent->name);

        return KidsParentForm::createFromModel($parent)->renderForm();
    }

    public function update(KidsParent $parent, KidsParentRequest $request)
    {
        abort_unless(Auth::user()->hasPermission('kids.parents.edit'), 403);
        $data = $request->validated();
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $parent->update($data);

        return $this
            ->httpResponse()
            ->setPreviousRoute('kids.parents.index')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(KidsParent $parent)
    {
        abort_unless(Auth::user()->hasPermission('kids.parents.destroy'), 403);

        return DeleteResourceAction::make($parent);
    }
}
