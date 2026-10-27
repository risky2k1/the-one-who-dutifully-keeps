<?php

namespace Botble\Kids\Tests;

use Botble\Kids\Providers\KidsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'Botble\\Kids\\Tests\\')) {
        $file = __DIR__.'/'.str_replace('\\', '/', substr($class, strlen('Botble\\Kids\\Tests\\'))).'.php';
        if (file_exists($file)) {
            require_once $file;
        }
    } elseif (str_starts_with($class, 'Botble\\Kids\\')) {
        $file = dirname(__DIR__).'/src/'.str_replace('\\', '/', substr($class, strlen('Botble\\Kids\\'))).'.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

abstract class KidsTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(KidsServiceProvider::class) && ! $this->app->providerIsLoaded(KidsServiceProvider::class)) {
            $this->app->register(KidsServiceProvider::class);
        }
    }
}
