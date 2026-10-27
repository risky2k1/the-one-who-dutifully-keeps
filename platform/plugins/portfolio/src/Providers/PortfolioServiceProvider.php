<?php

namespace Botble\Portfolio\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Portfolio\Models\PortfolioCategory;
use Botble\Portfolio\Models\PortfolioProject;
use Botble\Portfolio\Models\PortfolioSkillGroup;
use Botble\Setting\PanelSections\SettingOthersPanelSection;
use Botble\Slug\Facades\SlugHelper;

class PortfolioServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/portfolio')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadRoutes(['web', 'api']);

        SlugHelper::registering(function (): void {
            SlugHelper::registerModule(PortfolioProject::class, fn () => 'Portfolio Projects');
            SlugHelper::registerModule(PortfolioCategory::class, fn () => 'Portfolio Categories');
            SlugHelper::registerModule(PortfolioSkillGroup::class, fn () => 'Portfolio Skills');
            SlugHelper::setPrefix(PortfolioProject::class, 'portfolio-projects', true);
            SlugHelper::setPrefix(PortfolioCategory::class, 'portfolio-categories', true);
            SlugHelper::setPrefix(PortfolioSkillGroup::class, 'portfolio-skills', true);
        });

        DashboardMenu::default()->beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-portfolio')
                        ->priority(130)
                        ->name('Portfolio')
                        ->icon('ti ti-briefcase')
                        ->permissions(['portfolio.index'])
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-portfolio-projects')
                        ->parentId('cms-plugins-portfolio')
                        ->priority(10)
                        ->name('Projects')
                        ->icon('ti ti-folder')
                        ->route('portfolio.projects.index')
                        ->permissions(['portfolio.projects.index'])
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-portfolio-categories')
                        ->parentId('cms-plugins-portfolio')
                        ->priority(15)
                        ->name('Categories')
                        ->icon('ti ti-tag')
                        ->route('portfolio.categories.index')
                        ->permissions(['portfolio.categories.index'])
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-portfolio-skills')
                        ->parentId('cms-plugins-portfolio')
                        ->priority(20)
                        ->name('Skills & Domains')
                        ->icon('ti ti-tool')
                        ->route('portfolio.skills.index')
                        ->permissions(['portfolio.skills.index'])
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-portfolio-experiences')
                        ->parentId('cms-plugins-portfolio')
                        ->priority(30)
                        ->name('Experiences')
                        ->icon('ti ti-history')
                        ->route('portfolio.experiences.index')
                        ->permissions(['portfolio.experiences.index'])
                );
        });

        PanelSectionManager::default()->beforeRendering(function (): void {
            PanelSectionManager::registerItem(
                SettingOthersPanelSection::class,
                fn () => PanelSectionItem::make('portfolio')
                    ->setTitle('Portfolio Settings')
                    ->withIcon('ti ti-briefcase')
                    ->withDescription('Configure demonstration persona, narrative, and showcase endpoints')
                    ->withPriority(130)
                    ->withRoute('portfolio.settings')
                    ->withPermission('portfolio.settings')
            );
        });
    }
}
