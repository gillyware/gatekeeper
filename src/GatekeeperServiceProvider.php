<?php

namespace Braxey\Gatekeeper;

use Braxey\Gatekeeper\Services\GatekeeperService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class GatekeeperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishing();
        $this->registerBladeDirectives();
        $this->registerMiddleware();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();

        $this->app->singleton('gatekeeper', fn () => new GatekeeperService);
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/gatekeeper.php' => config_path('gatekeeper.php'),
        ], 'gatekeeper-config');

        $publishesMigrationsMethod = method_exists($this, 'publishesMigrations')
            ? 'publishesMigrations'
            : 'publishes';

        $this->{$publishesMigrationsMethod}([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'gatekeeper-migrations');
    }

    /**
     * Register the Blade directives for Gatekeeper.
     *
     * @return void
     */
    protected function registerBladeDirectives()
    {
        Blade::if('hasPermission', function (...$args) {
            $user = count($args) === 2 ? $args[0] : auth()->user();
            $permissionName = count($args) === 2 ? $args[1] : $args[0];

            return $user && method_exists($user, 'hasPermission') && $user->hasPermission($permissionName);
        });

        Blade::if('hasRole', function (...$args) {
            $user = count($args) === 2 ? $args[0] : auth()->user();
            $roleName = count($args) === 2 ? $args[1] : $args[0];

            return $user && method_exists($user, 'hasRole') && $user->hasRole($roleName);
        });

        Blade::if('onTeam', function (...$args) {
            $user = count($args) === 2 ? $args[0] : auth()->user();
            $teamName = count($args) === 2 ? $args[1] : $args[0];

            return $user && method_exists($user, 'onTeam') && $user->onTeam($teamName);
        });
    }

    /**
     * Register the middleware for Gatekeeper.
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        $router = $this->app->make('router');

        $router->aliasMiddleware('has_permission', \Braxey\Gatekeeper\Http\Middleware\HasPermission::class);
        $router->aliasMiddleware('has_role', \Braxey\Gatekeeper\Http\Middleware\HasRole::class);
        $router->aliasMiddleware('on_team', \Braxey\Gatekeeper\Http\Middleware\OnTeam::class);
    }

    /**
     * Setup the configuration for Gatekeeper.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/gatekeeper.php', 'gatekeeper'
        );
    }
}
