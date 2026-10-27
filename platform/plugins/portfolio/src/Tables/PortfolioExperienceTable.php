<?php

namespace Botble\Portfolio\Tables;

use Botble\Portfolio\Models\PortfolioExperience;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\StatusColumn;

class PortfolioExperienceTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(PortfolioExperience::class)
            ->addActions([
                EditAction::make()->route('portfolio.experiences.edit'),
                DeleteAction::make()->route('portfolio.experiences.destroy'),
            ])
            ->addColumns([
                IdColumn::make(),
                FormattedColumn::make('role')->title('Role / Title')->route('portfolio.experiences.edit'),
                FormattedColumn::make('organization')->title('Organization'),
                FormattedColumn::make('period')->title('Period'),
                StatusColumn::make(),
                CreatedAtColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('portfolio.experiences.destroy'),
            ]);
    }
}

