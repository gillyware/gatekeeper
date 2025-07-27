<?php

namespace Gillyware\Gatekeeper;

use Gillyware\Gatekeeper\Enums\GatekeeperPermission;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Repositories\CacheRepository;
use Gillyware\Gatekeeper\Repositories\FeatureRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasFeatureRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasRoleRepository;
use Gillyware\Gatekeeper\Repositories\ModelHasTeamRepository;
use Gillyware\Gatekeeper\Repositories\PermissionRepository;
use Gillyware\Gatekeeper\Repositories\RoleRepository;
use Gillyware\Gatekeeper\Repositories\TeamRepository;
use Gillyware\Gatekeeper\Services\AuditLogService;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\FeatureService;
use Gillyware\Gatekeeper\Services\GatekeeperForModelService;
use Gillyware\Gatekeeper\Services\GatekeeperService;
use Gillyware\Gatekeeper\Services\ModelHasFeatureService;
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
        $this->app->singleton(FeatureRepository::class);
        $this->app->singleton(TeamRepository::class);
        $this->app->singleton(ModelHasPermissionRepository::class);
        $this->app->singleton(ModelHasRoleRepository::class);
        $this->app->singleton(ModelHasFeatureRepository::class);
        $this->app->singleton(ModelHasTeamRepository::class);
        $this->app->singleton(AuditLogRepository::class);

        $this->app->singleton(PermissionService::class);
        $this->app->singleton(RoleService::class);
        $this->app->singleton(FeatureService::class);
        $this->app->singleton(TeamService::class);
        $this->app->singleton(ModelHasPermissionService::class);
        $this->app->singleton(ModelHasRoleService::class);
        $this->app->singleton(ModelHasFeatureService::class);
        $this->app->singleton(ModelHasTeamService::class);
        $this->app->singleton(AuditLogService::class);
        $this->app->singleton(GatekeeperForModelService::class);

        $this->app->singleton('gatekeeper', fn ($app) => new GatekeeperService(
            $app->make(PermissionService::class),
            $app->make(RoleService::class),
            $app->make(FeatureService::class),
            $app->make(TeamService::class),
            $app->make(GatekeeperForModelService::class),
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

        Route::middleware(['web', 'auth', 'has_permission:'.GatekeeperPermission::View->value])
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
                return Gatekeeper::for($model)->hasPermission($permission);
            }

            [$model, $permission] = [Auth::user(), $model];

            return Gatekeeper::for($model)->hasPermission($permission);
        });

        Blade::if('hasAnyPermission', function ($model, $permissions = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::for($model)->hasAnyPermission($permissions);
            }

            [$model, $permissions] = [Auth::user(), $model];

            return Gatekeeper::for($model)->hasAnyPermission($permissions);
        });

        Blade::if('hasAllPermissions', function ($model, $permissions = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::for($model)->hasAllPermissions($permissions);
            }

            [$model, $permissions] = [Auth::user(), $model];

            return Gatekeeper::for($model)->hasAllPermissions($permissions);
        });

        /**
         * Roles.
         */
        Blade::if('hasRole', function ($model, $role = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::for($model)->hasRole($role);
            }

            [$model, $role] = [Auth::user(), $model];

            return Gatekeeper::for($model)->hasRole($role);
        });

        Blade::if('hasAnyRole', function ($model, $roles = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::for($model)->hasAnyRole($roles);
            }

            [$model, $roles] = [Auth::user(), $model];

            return Gatekeeper::for($model)->hasAnyRole($roles);
        });

        Blade::if('hasAllRoles', function ($model, $roles = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::for($model)->hasAllRoles($roles);
            }

            [$model, $roles] = [Auth::user(), $model];

            return Gatekeeper::for($model)->hasAllRoles($roles);
        });

        /**
         * Features.
         */
        Blade::if('hasFeature', function ($model, $feature = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::for($model)->hasFeature($feature);
            }

            [$model, $feature] = [Auth::user(), $model];

            return Gatekeeper::for($model)->hasFeature($feature);
        });

        Blade::if('hasAnyFeature', function ($model, $features = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::for($model)->hasAnyFeature($features);
            }

            [$model, $features] = [Auth::user(), $model];

            return Gatekeeper::for($model)->hasAnyFeature($features);
        });

        Blade::if('hasAllFeatures', function ($model, $features = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::for($model)->hasAllFeatures($features);
            }

            [$model, $features] = [Auth::user(), $model];

            return Gatekeeper::for($model)->hasAllFeatures($features);
        });

        /**
         * Teams.
         */
        Blade::if('onTeam', function ($model, $team = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::for($model)->onTeam($team);
            }

            [$model, $team] = [Auth::user(), $model];

            return Gatekeeper::for($model)->onTeam($team);
        });

        Blade::if('onAnyTeam', function ($model, $teams = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::for($model)->onAnyTeam($teams);
            }

            [$model, $teams] = [Auth::user(), $model];

            return Gatekeeper::for($model)->onAnyTeam($teams);
        });

        Blade::if('onAllTeams', function ($model, $teams = null) {
            if (func_num_args() === 2) {
                return Gatekeeper::for($model)->onAllTeams($teams);
            }

            [$model, $teams] = [Auth::user(), $model];

            return Gatekeeper::for($model)->onAllTeams($teams);
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
        $router->aliasMiddleware('has_feature', \Gillyware\Gatekeeper\Http\Middleware\HasFeature::class);
        $router->aliasMiddleware('on_team', \Gillyware\Gatekeeper\Http\Middleware\OnTeam::class);

        $router->aliasMiddleware('has_any_permission', \Gillyware\Gatekeeper\Http\Middleware\HasAnyPermission::class);
        $router->aliasMiddleware('has_any_role', \Gillyware\Gatekeeper\Http\Middleware\HasAnyRole::class);
        $router->aliasMiddleware('has_any_feature', \Gillyware\Gatekeeper\Http\Middleware\HasAnyFeature::class);
        $router->aliasMiddleware('on_any_team', \Gillyware\Gatekeeper\Http\Middleware\OnAnyTeam::class);

        $router->aliasMiddleware('has_all_permissions', \Gillyware\Gatekeeper\Http\Middleware\HasAllPermissions::class);
        $router->aliasMiddleware('has_all_roles', \Gillyware\Gatekeeper\Http\Middleware\HasAllRoles::class);
        $router->aliasMiddleware('has_all_features', \Gillyware\Gatekeeper\Http\Middleware\HasAllFeatures::class);
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
            \Gillyware\Gatekeeper\Console\FeatureCommand::class,
            \Gillyware\Gatekeeper\Console\TeamCommand::class,
            \Gillyware\Gatekeeper\Console\ClearCacheCommand::class,
        ]);
    }
}
