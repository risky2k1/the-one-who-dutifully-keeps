<?php

namespace Botble\Kids\Tables;

use Botble\Kids\Models\KidsWordTemplate;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

class KidsWordTemplateTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(KidsWordTemplate::class)
            ->addActions([
                EditAction::make()->route('kids.word-templates.edit'),
                DeleteAction::make()->route('kids.word-templates.destroy'),
            ])
            ->addHeaderAction(CreateHeaderAction::make()->route('kids.word-templates.create'))
            ->addColumns([
                IdColumn::make(),
                FormattedColumn::make('template')
                    ->title('Template')
                    ->getValueUsing(fn (FormattedColumn $col) => mb_substr($col->getItem()->template, 0, 80).(mb_strlen($col->getItem()->template) > 80 ? '...' : '')),
                FormattedColumn::make('grade')
                    ->title('Grade')
                    ->getValueUsing(fn (FormattedColumn $col) => 'Grade '.$col->getItem()->grade),
                FormattedColumn::make('operation')
                    ->title('Operation')
                    ->getValueUsing(fn (FormattedColumn $col) => strtoupper((string) ($col->getItem()->operation?->getValue() ?? $col->getItem()->operation))),
                FormattedColumn::make('topic')
                    ->title('Topic')
                    ->getValueUsing(fn (FormattedColumn $col) => $col->getItem()->topic?->name ?: '—'),
                FormattedColumn::make('unit')
                    ->title('Unit')
                    ->getValueUsing(fn (FormattedColumn $col) => $col->getItem()->unit ?: '—'),
                StatusColumn::make(),
                CreatedAtColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('kids.word-templates.destroy'),
            ]);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'grade',
                'topic_id',
                'operation',
                'template',
                'unit',
                'status',
                'created_at',
            ])
            ->with(['topic']);

        return $this->applyScopes($query);
    }
}
