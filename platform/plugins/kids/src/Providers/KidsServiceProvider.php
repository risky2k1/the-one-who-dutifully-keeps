<?php

namespace Botble\Kids\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Kids\Engine\GeneratorRegistry;
use Botble\Kids\Engine\Generators\ArithmeticGenerator;
use Botble\Kids\Engine\Generators\ComparisonGenerator;
use Botble\Kids\Engine\Generators\CubeCountGenerator;
use Botble\Kids\Engine\Generators\WordProblemGenerator;

class KidsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
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
    }
}
