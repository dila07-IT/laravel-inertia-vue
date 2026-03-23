<?php

namespace Dilansabah\LaravelInertiaVue;

use Illuminate\Support\ServiceProvider;
use Dilansabah\LaravelInertiaVue\Console\InstallCommand;

class InertiaVueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}
