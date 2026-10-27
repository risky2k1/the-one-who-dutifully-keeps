<?php

namespace Botble\Kids\Tables;

use Botble\Kids\Models\KidsParent;
use Botble\Table\Abstracts\TableAbstract;
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

class KidsParentTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(KidsParent::class)
            ->addActions([
                EditAction::make()->route('kids.parents.edit'),
                DeleteAction::make()->route('kids.parents.destroy'),
            ])
            ->addHeaderAction(CreateHeaderAction::make()->route('kids.parents.create'))
            ->addColumns([
                IdColumn::make(),
                NameColumn::make()->route('kids.parents.edit'),
                FormattedColumn::make('username')
                    ->title('Username')
                    ->getValueUsing(fn (FormattedColumn $col) => $col->getItem()->username),
                FormattedColumn::make('children_count')
                    ->title('Children')
                    ->getValueUsing(fn (FormattedColumn $col) => (string) $col->getItem()->children()->count()),
                FormattedColumn::make('has_pin')
                    ->title('PIN Set')
                    ->getValueUsing(fn (FormattedColumn $col) => $col->getItem()->hasPin() ? 'Yes' : 'No'),
                StatusColumn::make(),
                CreatedAtColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('kids.parents.destroy'),
            ]);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this
            ->getModel()
            ->query()
            ->select([
                'id',
                'name',
                'username',
                'pin_hash',
                'status',
                'created_at',
            ])
            ->with(['children']);

        return $this->applyScopes($query);
    }
}
