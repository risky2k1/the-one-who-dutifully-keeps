<?php

namespace Botble\Kids\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Kids\Engine\GeneratorRegistry;
use Botble\Kids\Engine\Generators\ArithmeticGenerator;
use Botble\Kids\Engine\Generators\ComparisonGenerator;
use Botble\Kids\Engine\Generators\CubeCountGenerator;
use Botble\Kids\Engine\Generators\WordProblemGenerator;
use Botble\Kids\Http\Middleware\EnsureKidsToken;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\RateLimiter;

class KidsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('kids.token', EnsureKidsToken::class);

        $this->app->singleton(GeneratorRegistry::class, function () {
            $registry = new GeneratorRegistry;
            $arithmetic = new ArithmeticGenerator;
            $registry->register($arithmetic);
            $registry->register(new ComparisonGenerator);
            $registry->register(new CubeCountGenerator);
            $registry->register(new WordProblemGenerator($arithmetic));

            return $registry;
        });
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/kids')
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadMigrations()
            ->loadRoutes(['web', 'api']);

        DashboardMenu::default()->beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-kids')
                        ->priority(140)
                        ->name('Kids Math')
                        ->icon('ti ti-mood-kid')
                        ->permissions(['kids.index'])
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-kids-topics')
                        ->parentId('cms-plugins-kids')
                        ->priority(10)
                        ->name('Topics')
                        ->icon('ti ti-books')
                        ->route('kids.topics.index')
                        ->permissions(['kids.topics.index'])
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-kids-word-templates')
                        ->parentId('cms-plugins-kids')
                        ->priority(20)
                        ->name('Word Templates')
                        ->icon('ti ti-file-text')
                        ->route('kids.word-templates.index')
                        ->permissions(['kids.word-templates.index'])
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-kids-parents')
                        ->parentId('cms-plugins-kids')
                        ->priority(30)
                        ->name('Parents')
                        ->icon('ti ti-users')
                        ->route('kids.parents.index')
                        ->permissions(['kids.parents.index'])
                )
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-plugins-kids-reports')
                        ->parentId('cms-plugins-kids')
                        ->priority(40)
                        ->name('Reports')
                        ->icon('ti ti-alert-triangle')
                        ->route('kids.reports.index')
                        ->permissions(['kids.reports.index'])
                );
        });

        RateLimiter::for('kids-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip().'|'.(string) $request->input('username'));
        });

        RateLimiter::for('kids-pin', function (Request $request) {
            $token = $request->user()?->currentAccessToken();

            return Limit::perMinute(5)->by($token ? (string) $token->id : (string) $request->ip());
        });
    }
}
