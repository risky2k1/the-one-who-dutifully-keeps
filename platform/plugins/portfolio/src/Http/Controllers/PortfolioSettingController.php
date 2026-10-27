<?php

namespace Botble\Portfolio\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\Breadcrumb;
use Botble\Portfolio\Forms\PortfolioSettingForm;
use Botble\Portfolio\Http\Requests\PortfolioSettingRequest;
use Botble\Setting\Http\Controllers\SettingController;

class PortfolioSettingController extends SettingController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add('Portfolio Settings', route('portfolio.settings'));
    }

    public function edit()
    {
        $this->pageTitle('Portfolio Settings & Profile');

        return PortfolioSettingForm::create()->renderForm();
    }

    public function update(PortfolioSettingRequest $request): BaseHttpResponse
    {
        return $this->performUpdate($request->validated());
    }
}
