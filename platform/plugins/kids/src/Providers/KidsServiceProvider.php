<?php

namespace Botble\Kids\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;

class KidsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        //
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
