<?php

namespace Gillyware\Gatekeeper;

use Gillyware\Gatekeeper\Enums\GatekeeperPermissionName;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\CacheRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasRoleRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasTeamRepository;
use Gillyware\Gatekeeper\Repositories\PermissionRepository;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Gillyware\Gatekeeper\Services\AuditLogService;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\GatekeeperService;
use Gillyware\Gatekeeper\Services\ModelHasPermissionService;
use Gillyware\Gatekeeper\Services\ModelHasRoleService;
use Gillyware\Gatekeeper\Services\ModelHasTeamService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;
use Gillyware\Gatekeeper\Services\PermissionService;
use Gillyware\Gatekeeper\Services\RoleService;
use Gillyware\Gatekeeper\Services\TeamService;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
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
        $this->registerRoutes();
        $this->registerResources();
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

        $this->app->singleton(CacheRepository::class);
        $this->app->singleton(CacheService::class);

        $this->app->singleton(ModelService::class);
        $this->app->singleton(ModelMetadataService::class);

        $this->app->singleton(PermissionRepository::class);
        $this->app->singleton(RoleRepository::class);
        $this->app->singleton(TeamRepository::class);
        $this->app->singleton(ModelHasPermissionRepository::class);
        $this->app->singleton(ModelHasRoleRepository::class);
        $this->app->singleton(ModelHasTeamRepository::class);
        $this->app->singleton(AuditLogRepository::class);

        $this->app->singleton(PermissionService::class);
        $this->app->singleton(RoleService::class);
        $this->app->singleton(TeamService::class);
        $this->app->singleton(ModelHasPermissionService::class);
        $this->app->singleton(ModelHasRoleService::class);
        $this->app->singleton(ModelHasTeamService::class);
        $this->app->singleton(AuditLogService::class);

        $this->app->singleton('gatekeeper', fn ($app) => new GatekeeperService(
            $app->make(PermissionService::class),
            $app->make(RoleService::class),
            $app->make(TeamService::class),
        ));
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
     * Register the Gatekeeper routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if ($this->app instanceof CachesRoutes && $this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['web', 'auth', 'has_permission:'.GatekeeperPermissionName::View->value])
            ->namespace('Gillyware\Gatekeeper\Http\Controllers')
            ->name('gatekeeper.')
            ->group(__DIR__.'/../routes/web.php');
    }

    /**
     * Register the Gatekeeper resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'gatekeeper');
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
        Blade::if('hasPermission', function ($model, $permission = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::modelHasPermission($model, $permission);
            }

            [$model, $permission] = [Auth::user(), $model];

            return Gatekeeper::modelHasPermission($model, $permission);
        });

        Blade::if('hasAnyPermission', function ($model, $permissions = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::modelHasAnyPermission($model, $permissions);
            }

            [$model, $permissions] = [Auth::user(), $model];

            return Gatekeeper::modelHasAnyPermission($model, $permissions);
        });

        Blade::if('hasAllPermissions', function ($model, $permissions = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::modelHasAllPermissions($model, $permissions);
            }

            [$model, $permissions] = [Auth::user(), $model];

            return Gatekeeper::modelHasAllPermissions($model, $permissions);
        });

        /**
         * Roles.
         */
        Blade::if('hasRole', function ($model, $role = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::modelHasRole($model, $role);
            }

            [$model, $role] = [Auth::user(), $model];

            return Gatekeeper::modelHasRole($model, $role);
        });

        Blade::if('hasAnyRole', function ($model, $roles = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::modelHasAnyRole($model, $roles);
            }

            [$model, $roles] = [Auth::user(), $model];

            return Gatekeeper::modelHasAnyRole($model, $roles);
        });

        Blade::if('hasAllRoles', function ($model, $roles = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::modelHasAllRoles($model, $roles);
            }

            [$model, $roles] = [Auth::user(), $model];

            return Gatekeeper::modelHasAllRoles($model, $roles);
        });

        /**
         * Teams.
         */
        Blade::if('onTeam', function ($model, $team = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::modelOnTeam($model, $team);
            }

            [$model, $team] = [Auth::user(), $model];

            return Gatekeeper::modelOnTeam($model, $team);
        });

        Blade::if('onAnyTeam', function ($model, $teams = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::modelOnAnyTeam($model, $teams);
            }

            [$model, $teams] = [Auth::user(), $model];

            return Gatekeeper::modelOnAnyTeam($model, $teams);
        });

        Blade::if('onAllTeams', function ($model, $teams = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::modelOnAllTeams($model, $teams);
            }

            [$model, $teams] = [Auth::user(), $model];

            return Gatekeeper::modelOnAllTeams($model, $teams);
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

        $router->aliasMiddleware('has_permission', \Gillyware\Gatekeeper\Http\Middleware\HasPermission::class);
        $router->aliasMiddleware('has_role', \Gillyware\Gatekeeper\Http\Middleware\HasRole::class);
        $router->aliasMiddleware('on_team', \Gillyware\Gatekeeper\Http\Middleware\OnTeam::class);

        $router->aliasMiddleware('has_any_permission', \Gillyware\Gatekeeper\Http\Middleware\HasAnyPermission::class);
        $router->aliasMiddleware('has_any_role', \Gillyware\Gatekeeper\Http\Middleware\HasAnyRole::class);
        $router->aliasMiddleware('on_any_team', \Gillyware\Gatekeeper\Http\Middleware\OnAnyTeam::class);

        $router->aliasMiddleware('has_all_permissions', \Gillyware\Gatekeeper\Http\Middleware\HasAllPermissions::class);
        $router->aliasMiddleware('has_all_roles', \Gillyware\Gatekeeper\Http\Middleware\HasAllRoles::class);
        $router->aliasMiddleware('on_all_teams', \Gillyware\Gatekeeper\Http\Middleware\OnAllTeams::class);
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
            \Gillyware\Gatekeeper\Console\ListCommand::class,
            \Gillyware\Gatekeeper\Console\PermissionCommand::class,
            \Gillyware\Gatekeeper\Console\RoleCommand::class,
            \Gillyware\Gatekeeper\Console\TeamCommand::class,
            \Gillyware\Gatekeeper\Console\ClearCacheCommand::class,
        ]);
    }
}
