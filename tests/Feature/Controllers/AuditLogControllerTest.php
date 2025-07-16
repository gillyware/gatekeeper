<?php

namespace Gillyware\Gatekeeper\Tests\Feature\Controllers;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Database\Seeders\GatekeeperPermissionsSeeder;
use Gillyware\Gatekeeper\Enums\GatekeeperPermissionName;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class AuditLogControllerTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(GatekeeperPermissionsSeeder::class);
        $this->user = User::factory()->create();
        $this->be($this->user);

        $this->user->assignPermission(GatekeeperPermissionName::Manage);
    }

    public function test_returns_paginated_audit_logs()
    {
        $this->user->assignPermission(GatekeeperPermissionName::View);

        collect()->times(15)->each(function () {
            Gatekeeper::createPermission(fake()->unique()->word());
        });

        $response = $this->getJson(route('gatekeeper.api.audit-log.index', [
            'page' => 1,
            'created_at_order' => 'desc',
        ]));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'current_page',
                'data' => [['id', 'message', 'created_at']],
                'from',
                'last_page',
                'per_page',
                'to',
                'total',
            ])
            ->assertJsonCount(10, 'data');
    }

    public function test_returns_empty_data_when_no_logs()
    {
        $this->user->assignPermission(GatekeeperPermissionName::View);

        AuditLog::truncate();

        $response = $this->getJson(route('gatekeeper.api.audit-log.index', [
            'page' => 1,
            'created_at_order' => 'asc',
        ]));

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(['data' => []]);
    }

    public function test_fails_when_table_does_not_exist()
    {
        $this->user->assignPermission(GatekeeperPermissionName::View);

        Schema::drop(Config::get('gatekeeper.tables.audit_logs', GatekeeperConfigDefault::TABLES_AUDIT_LOGS));

        $response = $this->getJson(route('gatekeeper.api.audit-log.index', [
            'page' => 1,
            'created_at_order' => 'asc',
        ]));

        $response
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonFragment(['message' => 'The audit log table does not exist in the database.']);
    }

    public function test_fails_without_manage_permission()
    {
        $this->getJson(route('gatekeeper.api.audit-log.index', [
            'page' => 1,
            'created_at_order' => 'asc',
        ]))->assertStatus(Response::HTTP_BAD_REQUEST);
    }
}
