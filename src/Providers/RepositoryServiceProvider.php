<?php

namespace Fomvasss\Repository\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/repository.php' => config_path('repository.php')
        ], 'repository-config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/repository.php', 'repository');

        //$this->commands('Fomvasss\Repository\Generators\Commands\RepositoryCommand');
    }
}
