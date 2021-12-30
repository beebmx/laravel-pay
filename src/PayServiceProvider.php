<?php

namespace Beebmx\LaravelPay;

use Beebmx\LaravelPay\Console\Webhook as WebhookCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PayServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerMigrations();
        $this->registerPublishing();
        $this->registerCommands();
        $this->registerRoutes();
    }

    public function register()
    {
        $this->config();
    }

    /**
     * Setup the configuration
     *
     * @return void
     */
    protected function config()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/pay.php',
            'pay'
        );
    }

    /**
     * Register migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (Pay::$runsMigrations && $this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Register publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/pay.php' => $this->app->configPath('pay.php'),
            ], 'pay-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'pay-migrations');

//            $this->publishes([
//                __DIR__.'/../resources/views' => $this->app->resourcePath('views/vendor/pay'),
//            ], 'pay-views');
        }
    }

    protected function registerRoutes()
    {
        if (Pay::$registersRoutes) {
            Route::group(['prefix' => config('pay.path'), 'as' => 'pay.'], function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            });
        }
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WebhookCommand::class,
            ]);
        }
    }
}
