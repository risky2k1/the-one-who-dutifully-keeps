<?php

namespace Botble\Kids\Providers;

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
            ->loadMigrations()
            ->loadRoutes(['web', 'api']);

        RateLimiter::for('kids-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip().'|'.(string) $request->input('username'));
        });

        RateLimiter::for('kids-pin', function (Request $request) {
            $token = $request->user()?->currentAccessToken();

            return Limit::perMinute(5)->by($token ? (string) $token->id : (string) $request->ip());
        });
    }
}
