<?php

namespace Botble\Portfolio\Tables;

use Botble\Portfolio\Models\PortfolioSkillGroup;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\Columns\StatusColumn;

class PortfolioSkillGroupTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(PortfolioSkillGroup::class)
            ->addActions([
                EditAction::make()->route('portfolio.skills.edit'),
                DeleteAction::make()->route('portfolio.skills.destroy'),
            ])
            ->addColumns([
                IdColumn::make(),
                NameColumn::make()->route('portfolio.skills.edit'),
                FormattedColumn::make('badge')->title('Category Badge'),
                FormattedColumn::make('skills_count')
                    ->title('Total Skills')
                    ->getValueUsing(fn (FormattedColumn $col) => $col->getItem()->skills()->count()),
                StatusColumn::make(),
                CreatedAtColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('portfolio.skills.destroy'),
            ]);
    }
}

