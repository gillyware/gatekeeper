<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Packets\Builders\StoreAuditLogPacketBuilder;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;

class AuditLogRepositoryTest extends TestCase
{
    public function test_create_persists_audit_log()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        Gatekeeper::actingAs($user);

        $auditLog = StoreAuditLogPacketBuilder::action(AuditLogAction::DeactivatePermission)
            ->setActionToEntity($permission)
            ->store();

        $this->assertDatabaseHas((new AuditLog)->getTable(), [
            'id' => $auditLog->id,
            'action' => AuditLogAction::DeactivatePermission->value,
            'action_by_model_type' => $user->getMorphClass(),
            'action_by_model_id' => $user->getKey(),
            'action_to_model_type' => $permission->getMorphClass(),
            'action_to_model_id' => $permission->getKey(),
        ]);
    }
}
