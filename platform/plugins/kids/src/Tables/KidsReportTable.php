<?php

namespace Botble\Kids\Tables;

use Botble\Kids\Models\KidsReport;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

class KidsReportTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(KidsReport::class)
            ->addActions([
                Action::make('resolve')
                    ->label('Resolved')
                    ->icon('ti ti-check')
                    ->color('success')
                    ->url(fn (Action $action) => route('kids.reports.resolve', $action->getItem()->getKey())),
                DeleteAction::make()->route('kids.reports.destroy'),
            ])
            ->addColumns([
                IdColumn::make(),
                FormattedColumn::make('topic')
                    ->title('Topic')
                    ->getValueUsing(fn (FormattedColumn $col) => $col->getItem()->question?->topic?->name ?: '—'),
                FormattedColumn::make('prompt')
                    ->title('Question Prompt')
                    ->getValueUsing(function (FormattedColumn $col) {
                        $prompt = $col->getItem()->question?->prompt;

                        return is_array($prompt) ? ($prompt['text'] ?? json_encode($prompt, JSON_UNESCAPED_UNICODE)) : '—';
                    }),
                FormattedColumn::make('params')
                    ->title('Parameters')
                    ->getValueUsing(function (FormattedColumn $col) {
                        $params = $col->getItem()->question?->params;

                        return $params ? json_encode($params, JSON_UNESCAPED_UNICODE) : '—';
                    }),
                FormattedColumn::make('note')
                    ->title('Parent Note')
                    ->getValueUsing(fn (FormattedColumn $col) => $col->getItem()->note ?: '—'),
                FormattedColumn::make('parent')
                    ->title('Reporting Parent')
                    ->getValueUsing(fn (FormattedColumn $col) => $col->getItem()->parent?->name ?: '—'),
                FormattedColumn::make('status')
                    ->title('Status')
                    ->getValueUsing(function (FormattedColumn $col) {
                        $val = (string) ($col->getItem()->status?->getValue() ?? $col->getItem()->status);
                        $badge = $val === 'resolved' ? 'success' : ($val === 'dismissed' ? 'secondary' : 'warning');

                        return '<span class="badge bg-'.$badge.'">'.ucfirst($val).'</span>';
                    }),
                CreatedAtColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('kids.reports.destroy'),
            ]);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'question_id',
                'parent_id',
                'note',
                'status',
                'created_at',
            ])
            ->with(['question.topic', 'parent']);

        return $this->applyScopes($query);
    }
}
