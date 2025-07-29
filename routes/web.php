<?php

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Enums\GatekeeperPermission;
use Gillyware\Gatekeeper\Http\Controllers\AuditLogController;
use Gillyware\Gatekeeper\Http\Controllers\FeatureController;
use Gillyware\Gatekeeper\Http\Controllers\LandingController;
use Gillyware\Gatekeeper\Http\Controllers\ModelController;
use Gillyware\Gatekeeper\Http\Controllers\PermissionController;
use Gillyware\Gatekeeper\Http\Controllers\RoleController;
use Gillyware\Gatekeeper\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

Route::get(Config::get('gatekeeper.path', GatekeeperConfigDefault::PATH), LandingController::class)->name('gatekeeper.landing');

Route::prefix('gatekeeper/api')->name('gatekeeper.api.')->group(function () {

    /**
     * ******************************************************************
     * Audit Logs
     * ******************************************************************
     */
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

    /**
     * ******************************************************************
     * Permissions
     * ******************************************************************
     */
    Route::prefix('permissions')->name('permissions.')->controller(PermissionController::class)->group(function () {

        Route::get('/', 'index')->name('index');

        Route::get('{permission}', 'show')->name('show');

        Route::middleware('has_permission:'.GatekeeperPermission::Manage->value)->group(function () {

            Route::post('/', 'store')->name('store');

            Route::patch('{permission}', 'update')->name('update');

            Route::delete('{permission}', 'delete')->name('delete');

        });

    });

    /**
     * ******************************************************************
     * Roles
     * ******************************************************************
     */
    Route::prefix('roles')->name('roles.')->controller(RoleController::class)->group(function () {

        Route::get('/', 'index')->name('index');

        Route::get('{role}', 'show')->name('show');

        Route::middleware('has_permission:'.GatekeeperPermission::Manage->value)->group(function () {

            Route::post('/', 'store')->name('store');

            Route::patch('{role}', 'update')->name('update');

            Route::delete('{role}', 'delete')->name('delete');

        });

    });

    /**
     * ******************************************************************
     * Features
     * ******************************************************************
     */
    Route::prefix('features')->name('features.')->controller(FeatureController::class)->group(function () {

        Route::get('/', 'index')->name('index');

        Route::get('{feature}', 'show')->name('show');

        Route::middleware('has_permission:'.GatekeeperPermission::Manage->value)->group(function () {

            Route::post('/', 'store')->name('store');

            Route::patch('{feature}', 'update')->name('update');

            Route::delete('{feature}', 'delete')->name('delete');

        });

    });

    /**
     * ******************************************************************
     * Teams
     * ******************************************************************
     */
    Route::prefix('teams')->name('teams.')->controller(TeamController::class)->group(function () {

        Route::get('/', 'index')->name('index');

        Route::get('{team}', 'show')->name('show');

        Route::middleware('has_permission:'.GatekeeperPermission::Manage->value)->group(function () {

            Route::post('/', 'store')->name('store');

            Route::patch('{team}', 'update')->name('update');

            Route::delete('{team}', 'delete')->name('delete');

        });

    });

    /**
     * ******************************************************************
     * Models
     * ******************************************************************
     */
    Route::prefix('models')->name('models.')->controller(ModelController::class)->group(function () {

        Route::get('/', 'index')->name('index');

        Route::get('/{modelLabel}/{modelPk}', 'show')->name('show');

        Route::get('/{modelLabel}/{modelPk}/entities/{entity}/assigned', 'searchAssignedEntitiesForModel')->name('search-assigned-entities');

        Route::get('/{modelLabel}/{modelPk}/entities/{entity}/unassigned', 'searchUnassignedEntitiesForModel')->name('search-unassigned-entities');

        Route::get('/{modelLabel}/{modelPk}/entities/{entity}/denied', 'searchDeniedEntitiesForModel')->name('search-denied-entities');

        Route::middleware('has_permission:'.GatekeeperPermission::Manage->value)->group(function () {

            Route::post('/{modelLabel}/{modelPk}/entities/{entity}/assign', 'assign')->name('assign');

            Route::delete('/{modelLabel}/{modelPk}/entities/{entity}/unassign', 'unassign')->name('unassign');

            Route::post('/{modelLabel}/{modelPk}/entities/{entity}/deny', 'deny')->name('deny');

            Route::delete('/{modelLabel}/{modelPk}/entities/{entity}/undeny', 'undeny')->name('undeny');

        });

    });
});
