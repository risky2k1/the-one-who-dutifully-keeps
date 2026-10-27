<?php

namespace Botble\Portfolio\Tables;

use Botble\Portfolio\Models\PortfolioProject;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\HtmlString;

class PortfolioProjectTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(PortfolioProject::class)
            ->addActions([
                EditAction::make()->route('portfolio.projects.edit'),
                DeleteAction::make()->route('portfolio.projects.destroy'),
            ])
            ->addColumns([
                IdColumn::make(),
                NameColumn::make()->route('portfolio.projects.edit'),
                FormattedColumn::make('categories')
                    ->title('Categories')
                    ->getValueUsing(function (FormattedColumn $col) {
                        /** @var PortfolioProject $item */
                        $item = $col->getItem();
                        $cats = $item->categories;

                        if ($cats && $cats->isNotEmpty()) {
                            $badges = $cats->map(function ($cat) {
                                return sprintf(
                                    '<span class="badge" style="background-color: %s25; color: #0f766e; border: 1px solid %s; margin-right: 4px;">%s</span>',
                                    e($cat->accent_color ?: '#5ec2b7'),
                                    e($cat->accent_color ?: '#5ec2b7'),
                                    e($cat->name)
                                );
                            })->implode('');

                            return new HtmlString($badges);
                        }

                        return $item->category ?: '—';
                    }),
                FormattedColumn::make('timeline')->title('Timeline'),
                StatusColumn::make(),
                CreatedAtColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('portfolio.projects.destroy'),
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
                'category',
                'timeline',
                'status',
                'created_at',
            ])
            ->with(['categories']);

        return $this->applyScopes($query);
    }
}
