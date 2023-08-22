<?php

namespace Beebmx\LaravelPay;

use Beebmx\LaravelPay\Console\Webhook as WebhookCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PayServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerMigrations();
        $this->registerPublishing();
        $this->registerCommands();
        $this->registerRoutes();
    }

    public function register(): void
    {
        $this->config();
    }

    /**
     * Setup the configuration
     */
    protected function config(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/pay.php',
            'pay'
        );
    }

    /**
     * Register migrations.
     */
    protected function registerMigrations(): void
    {
        if (Pay::$runsMigrations && $this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/pay.php' => $this->app->configPath('pay.php'),
            ], 'pay-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'pay-migrations');

            //            $this->publishes([
            //                __DIR__.'/../resources/views' => $this->app->resourcePath('views/vendor/pay'),
            //            ], 'pay-views');
        }
    }

    protected function registerRoutes(): void
    {
        if (Pay::$registersRoutes) {
            Route::group(['prefix' => config('pay.path'), 'as' => 'pay.'], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
        }
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WebhookCommand::class,
            ]);
        }
    }
}
