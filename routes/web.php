<?php

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Enums\GatekeeperPermissionName;
use Gillyware\Gatekeeper\Http\Controllers\AuditLogController;
use Gillyware\Gatekeeper\Http\Controllers\HomeController;
use Gillyware\Gatekeeper\Http\Controllers\ModelController;
use Gillyware\Gatekeeper\Http\Controllers\PermissionController;
use Gillyware\Gatekeeper\Http\Controllers\RoleController;
use Gillyware\Gatekeeper\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

Route::get(Config::get('gatekeeper.path', GatekeeperConfigDefault::PATH), HomeController::class)->name('home');

Route::prefix('gatekeeper/api')->name('api.')->group(function () {

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

        Route::middleware('has_permission:'.GatekeeperPermissionName::Manage->value)->group(function () {

            Route::post('/', 'store')->name('store');

            Route::put('{permission}', 'update')->name('update');

            Route::patch('{permission}/deactivate', 'deactivate')->name('deactivate');

            Route::patch('{permission}/reactivate', 'reactivate')->name('reactivate');

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

        Route::middleware('has_permission:'.GatekeeperPermissionName::Manage->value)->group(function () {

            Route::post('/', 'store')->name('store');

            Route::put('{role}', 'update')->name('update');

            Route::patch('{role}/deactivate', 'deactivate')->name('deactivate');

            Route::patch('{role}/reactivate', 'reactivate')->name('reactivate');

            Route::delete('{role}', 'delete')->name('delete');

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

        Route::middleware('has_permission:'.GatekeeperPermissionName::Manage->value)->group(function () {

            Route::post('/', 'store')->name('store');

            Route::put('{team}', 'update')->name('update');

            Route::patch('{team}/deactivate', 'deactivate')->name('deactivate');

            Route::patch('{team}/reactivate', 'reactivate')->name('reactivate');

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

        Route::middleware('has_permission:'.GatekeeperPermissionName::Manage->value)->group(function () {

            Route::post('/{modelLabel}/{modelPk}/entities/{entity}/assign', 'assign')->name('assign');

            Route::delete('/{modelLabel}/{modelPk}/entities/{entity}/revoke', 'revoke')->name('revoke');

        });

    });
});
