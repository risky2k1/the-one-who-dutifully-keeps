<?php

namespace Botble\Kids\Tables;

use Botble\Kids\Models\KidsTopic;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;

class KidsTopicTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(KidsTopic::class)
            ->addActions([
                Action::make('preview')
                    ->label('Preview')
                    ->icon('ti ti-eye')
                    ->url(fn (Action $action) => route('kids.topics.preview', $action->getItem()->getKey())),
                EditAction::make()->route('kids.topics.edit'),
                DeleteAction::make()->route('kids.topics.destroy'),
            ])
            ->addHeaderAction(CreateHeaderAction::make()->route('kids.topics.create'))
            ->addColumns([
                IdColumn::make(),
                NameColumn::make()->route('kids.topics.edit'),
                FormattedColumn::make('code')
                    ->title('Code')
                    ->getValueUsing(fn (FormattedColumn $col) => $col->getItem()->code),
                FormattedColumn::make('grade')
                    ->title('Grade')
                    ->getValueUsing(fn (FormattedColumn $col) => 'Grade '.$col->getItem()->grade),
                FormattedColumn::make('parent')
                    ->title('Parent Topic')
                    ->getValueUsing(function (FormattedColumn $column) {
                        $parent = $column->getItem()->parent;

                        return $parent && $parent->name ? $parent->name : '— (Root)';
                    }),
                FormattedColumn::make('generator')
                    ->title('Generator')
                    ->getValueUsing(fn (FormattedColumn $col) => $col->getItem()->generator ?: '— (Category)'),
                StatusColumn::make(),
                CreatedAtColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('kids.topics.destroy'),
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
                'parent_id',
                'code',
                'name',
                'generator',
                'status',
                'created_at',
            ])
            ->with(['parent']);

        return $this->applyScopes($query);
    }
}
