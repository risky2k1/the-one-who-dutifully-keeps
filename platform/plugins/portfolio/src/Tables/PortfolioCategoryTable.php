<?php

namespace Botble\Portfolio\Tables;

use Botble\Portfolio\Models\PortfolioCategory;
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

class PortfolioCategoryTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(PortfolioCategory::class)
            ->addActions([
                EditAction::make()->route('portfolio.categories.edit'),
                DeleteAction::make()->route('portfolio.categories.destroy'),
            ])
            ->addHeaderAction(CreateHeaderAction::make()->route('portfolio.categories.create'))
            ->addColumns([
                IdColumn::make(),
                NameColumn::make()->route('portfolio.categories.edit'),
                FormattedColumn::make('parent')
                    ->title('Parent Category')
                    ->getValueUsing(function (FormattedColumn $column) {
                        $parent = $column->getItem()->parent;

                        return $parent && $parent->name ? $parent->name : '— (Root)';
                    }),
                FormattedColumn::make('badge')
                    ->title('Badge')
                    ->getValueUsing(fn (FormattedColumn $col) => $col->getItem()->badge ?: '—'),
                FormattedColumn::make('projects_count')
                    ->title('Projects')
                    ->getValueUsing(fn (FormattedColumn $col) => (string) $col->getItem()->projects()->count()),
                FormattedColumn::make('skills_count')
                    ->title('Skills')
                    ->getValueUsing(fn (FormattedColumn $col) => (string) $col->getItem()->skills()->count()),
                StatusColumn::make(),
                CreatedAtColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('portfolio.categories.destroy'),
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
                'slug',
                'parent_id',
                'badge',
                'accent_color',
                'order',
                'status',
                'created_at',
            ])
            ->with(['parent']);

        return $this->applyScopes($query);
    }
}
