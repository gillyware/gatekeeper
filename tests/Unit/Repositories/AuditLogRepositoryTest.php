<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Packets\AuditLog\Permission\DeactivatePermissionAuditLogPacket;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class AuditLogRepositoryTest extends TestCase
{
    public function test_create_persists_audit_log()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        Gatekeeper::actingAs($user);
        $packet = DeactivatePermissionAuditLogPacket::make($permission);

        $repository = new AuditLogRepository;
        $auditLog = $repository->create($packet);

        $this->assertDatabaseHas(Config::get('gatekeeper.tables.audit_log', GatekeeperConfigDefault::TABLES_AUDIT_LOG), [
            'id' => $auditLog->id,
            'action' => AuditLogAction::DeactivatePermission->value,
            'action_by_model_type' => $user->getMorphClass(),
            'action_by_model_id' => $user->getKey(),
            'action_to_model_type' => $permission->getMorphClass(),
            'action_to_model_id' => $permission->getKey(),
        ]);
    }
}
