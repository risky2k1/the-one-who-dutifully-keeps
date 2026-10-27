<?php

namespace Botble\Kids\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Kids\Enums\ReportStatusEnum;
use Botble\Kids\Models\KidsReport;
use Botble\Kids\Tables\KidsReportTable;
use Illuminate\Support\Facades\Auth;

class KidsReportController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add('Kids Math', route('kids.topics.index'))
            ->add('Reports', route('kids.reports.index'));
    }

    public function index(KidsReportTable $table)
    {
        abort_unless(Auth::user()->hasPermission('kids.reports.index'), 403);
        $this->pageTitle('Question Error Reports');

        return $table->renderTable();
    }

    public function resolve(KidsReport $report)
    {
        abort_unless(Auth::user()->hasPermission('kids.reports.edit'), 403);
        $report->update(['status' => ReportStatusEnum::RESOLVED]);

        return $this
            ->httpResponse()
            ->setPreviousRoute('kids.reports.index')
            ->setMessage('Report marked as resolved.')
            ->withUpdatedSuccessMessage();
    }

    public function destroy(KidsReport $report)
    {
        abort_unless(Auth::user()->hasPermission('kids.reports.destroy'), 403);

        return DeleteResourceAction::make($report);
    }
}
