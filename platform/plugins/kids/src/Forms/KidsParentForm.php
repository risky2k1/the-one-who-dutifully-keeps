<?php

namespace Botble\Kids\Forms;

use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\PasswordField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Kids\Http\Requests\KidsParentRequest;
use Botble\Kids\Models\KidsParent;

class KidsParentForm extends FormAbstract
{
    public function setup(): void
    {
        $isEdit = $this->getModel() && $this->getModel()->exists;

        $this
            ->model(KidsParent::class)
            ->setValidatorClass(KidsParentRequest::class)
            ->add('name', TextField::class, NameFieldOption::make()->label('Parent Name')->required())
            ->add('username', TextField::class, TextFieldOption::make()->label('Username')->required())
            ->add(
                'password',
                PasswordField::class,
                TextFieldOption::make()
                    ->label('Password')
                    ->required(! $isEdit)
                    ->placeholder($isEdit ? 'Leave blank to keep unchanged' : 'Enter account password')
            )
            ->add(
                'status',
                SelectField::class,
                StatusFieldOption::make()
                    ->choices([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
            )
            ->setBreakFieldPoint('status');

        if ($isEdit) {
            $children = $this->getModel()->children;
            $html = '<div class="card p-3 mb-3"><h5 class="card-title">Enrolled Children</h5>';
            if ($children->isEmpty()) {
                $html .= '<p class="text-muted mb-0">No children profiles registered yet.</p>';
            } else {
                $html .= '<ul class="list-group list-group-flush">';
                foreach ($children as $c) {
                    $html .= '<li class="list-group-item d-flex justify-content-between align-items-center">';
                    $html .= '<div><strong>'.e($c->name).'</strong> <span class="badge bg-secondary ms-2">Grade '.e($c->grade).'</span></div>';
                    $html .= '<span class="text-muted small">Avatar: '.e($c->avatar).' | Streak: '.e($c->streak_current).'</span>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }
            $html .= '</div>';

            $this->add('children_view', HtmlField::class, HtmlFieldOption::make()->content($html));
        }
    }
}
