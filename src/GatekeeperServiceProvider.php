<?php

namespace Braxey\Gatekeeper;

use Braxey\Gatekeeper\Services\GatekeeperService;
use Braxey\Gatekeeper\Services\PermissionService;
use Braxey\Gatekeeper\Services\RoleService;
use Braxey\Gatekeeper\Services\TeamService;
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
        $this->registerCommands();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();

        $this->app->singleton('gatekeeper', function ($app) {
            return new GatekeeperService(
                $app->make(PermissionService::class),
                $app->make(RoleService::class),
                $app->make(TeamService::class),
            );
        });
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
        /**
         * Permissions.
         */
        Blade::if('hasPermission', function (...$args) {
            $user = count($args) === 2 ? $args[0] : auth()->user();
            $permissionName = count($args) === 2 ? $args[1] : $args[0];

            return $user && method_exists($user, 'hasPermission') && $user->hasPermission($permissionName);
        });

        Blade::if('hasAnyPermission', function (...$args) {
            $user = count($args) === 2 ? $args[0] : auth()->user();
            $permissionNames = count($args) === 2 ? $args[1] : $args[0];

            return $user && method_exists($user, 'hasAnyPermission') && $user->hasAnyPermission($permissionNames);
        });

        Blade::if('hasAllPermissions', function (...$args) {
            $user = count($args) === 2 ? $args[0] : auth()->user();
            $permissionNames = count($args) === 2 ? $args[1] : $args[0];

            return $user && method_exists($user, 'hasAllPermissions') && $user->hasAllPermissions($permissionNames);
        });

        /**
         * Roles.
         */
        Blade::if('hasRole', function (...$args) {
            $user = count($args) === 2 ? $args[0] : auth()->user();
            $roleName = count($args) === 2 ? $args[1] : $args[0];

            return $user && method_exists($user, 'hasRole') && $user->hasRole($roleName);
        });

        Blade::if('hasAnyRole', function (...$args) {
            $user = count($args) === 2 ? $args[0] : auth()->user();
            $roleNames = count($args) === 2 ? $args[1] : $args[0];

            return $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roleNames);
        });

        Blade::if('hasAllRoles', function (...$args) {
            $user = count($args) === 2 ? $args[0] : auth()->user();
            $roleNames = count($args) === 2 ? $args[1] : $args[0];

            return $user && method_exists($user, 'hasAllRoles') && $user->hasAllRoles($roleNames);
        });

        /**
         * Teams.
         */
        Blade::if('onTeam', function (...$args) {
            $user = count($args) === 2 ? $args[0] : auth()->user();
            $teamName = count($args) === 2 ? $args[1] : $args[0];

            return $user && method_exists($user, 'onTeam') && $user->onTeam($teamName);
        });

        Blade::if('onAnyTeam', function (...$args) {
            $user = count($args) === 2 ? $args[0] : auth()->user();
            $teamNames = count($args) === 2 ? $args[1] : $args[0];

            return $user && method_exists($user, 'onAnyTeam') && $user->onAnyTeam($teamNames);
        });

        Blade::if('onAllTeams', function (...$args) {
            $user = count($args) === 2 ? $args[0] : auth()->user();
            $teamNames = count($args) === 2 ? $args[1] : $args[0];

            return $user && method_exists($user, 'onAllTeams') && $user->onAllTeams($teamNames);
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

        $router->aliasMiddleware('has_any_permission', \Braxey\Gatekeeper\Http\Middleware\HasAnyPermission::class);
        $router->aliasMiddleware('has_any_role', \Braxey\Gatekeeper\Http\Middleware\HasAnyRole::class);
        $router->aliasMiddleware('on_any_team', \Braxey\Gatekeeper\Http\Middleware\OnAnyTeam::class);

        $router->aliasMiddleware('has_all_permissions', \Braxey\Gatekeeper\Http\Middleware\HasAllPermissions::class);
        $router->aliasMiddleware('has_all_roles', \Braxey\Gatekeeper\Http\Middleware\HasAllRoles::class);
        $router->aliasMiddleware('on_all_teams', \Braxey\Gatekeeper\Http\Middleware\OnAllTeams::class);
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            \Braxey\Gatekeeper\Console\CreatePermissionCommand::class,
            \Braxey\Gatekeeper\Console\CreateRoleCommand::class,
            \Braxey\Gatekeeper\Console\CreateTeamCommand::class,
            \Braxey\Gatekeeper\Console\ListCommand::class,
            \Braxey\Gatekeeper\Console\RevokeCommand::class,
            \Braxey\Gatekeeper\Console\AssignCommand::class,
            \Braxey\Gatekeeper\Console\ClearCacheCommand::class,
        ]);
    }
}
