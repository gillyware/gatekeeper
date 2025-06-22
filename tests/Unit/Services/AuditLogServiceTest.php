<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Services;

use Gillyware\Gatekeeper\Constants\Action;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Services\AuditLogService;
use Gillyware\Gatekeeper\Support\SystemActor;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    protected AuditLogService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuditLogService;
    }

    public function test_create_message()
    {
        $log = new AuditLog([
            'action' => Action::PERMISSION_CREATE,
            'action_by_model_type' => User::class,
            'action_by_model_id' => 1,
            'action_to_model_type' => Permission::class,
            'action_to_model_id' => 99,
            'metadata' => ['name' => 'edit-users'],
        ]);

        $msg = $this->service->getMessageForAuditLog($log);
        $this->assertStringContainsString('created a new', $msg);
        $this->assertStringContainsString('edit-users', $msg);
    }

    public function test_update_message()
    {
        $log = new AuditLog([
            'action' => Action::PERMISSION_UPDATE,
            'action_by_model_type' => User::class,
            'action_by_model_id' => 2,
            'action_to_model_type' => Permission::class,
            'action_to_model_id' => 101,
            'metadata' => ['name' => 'edit-users', 'old_name' => 'manage-users'],
        ]);

        $msg = $this->service->getMessageForAuditLog($log);
        $this->assertStringContainsString('updated', $msg);
        $this->assertStringContainsString('manage-users', $msg);
        $this->assertStringContainsString('edit-users', $msg);
    }

    public function test_deactivate_message()
    {
        $log = new AuditLog([
            'action' => Action::PERMISSION_DEACTIVATE,
            'action_by_model_type' => User::class,
            'action_by_model_id' => 3,
            'action_to_model_type' => Permission::class,
            'action_to_model_id' => 102,
            'metadata' => ['name' => 'delete-users'],
        ]);

        $msg = $this->service->getMessageForAuditLog($log);
        $this->assertStringContainsString('deactivated', $msg);
        $this->assertStringContainsString('delete-users', $msg);
    }

    public function test_reactivate_message()
    {
        $log = new AuditLog([
            'action' => Action::PERMISSION_REACTIVATE,
            'action_by_model_type' => User::class,
            'action_by_model_id' => 4,
            'action_to_model_type' => Permission::class,
            'action_to_model_id' => 103,
            'metadata' => ['name' => 'restore-users'],
        ]);

        $msg = $this->service->getMessageForAuditLog($log);
        $this->assertStringContainsString('reactivated', $msg);
        $this->assertStringContainsString('restore-users', $msg);
    }

    public function test_delete_message()
    {
        $log = new AuditLog([
            'action' => Action::PERMISSION_DELETE,
            'action_by_model_type' => User::class,
            'action_by_model_id' => 5,
            'action_to_model_type' => Permission::class,
            'action_to_model_id' => 104,
            'metadata' => ['name' => 'archive-users'],
        ]);

        $msg = $this->service->getMessageForAuditLog($log);
        $this->assertStringContainsString('deleted', $msg);
        $this->assertStringContainsString('archive-users', $msg);
    }

    public function test_assign_message()
    {
        $log = new AuditLog([
            'action' => Action::PERMISSION_ASSIGN,
            'action_by_model_type' => User::class,
            'action_by_model_id' => 6,
            'action_to_model_type' => User::class,
            'action_to_model_id' => 10,
            'metadata' => ['name' => 'assign-perm'],
        ]);

        $msg = $this->service->getMessageForAuditLog($log);
        $this->assertStringContainsString('assigned', $msg);
        $this->assertStringContainsString('assign-perm', $msg);
        $this->assertStringContainsString('User#10', $msg);
    }

    public function test_revoke_message()
    {
        $log = new AuditLog([
            'action' => Action::ROLE_REVOKE,
            'action_by_model_type' => User::class,
            'action_by_model_id' => 7,
            'action_to_model_type' => User::class,
            'action_to_model_id' => 11,
            'metadata' => ['name' => 'admin-role'],
        ]);

        $msg = $this->service->getMessageForAuditLog($log);
        $this->assertStringContainsString('revoked', $msg);
        $this->assertStringContainsString('admin-role', $msg);
        $this->assertStringContainsString('User#11', $msg);
    }

    public function test_add_to_team_message()
    {
        $log = new AuditLog([
            'action' => Action::TEAM_ADD,
            'action_by_model_type' => User::class,
            'action_by_model_id' => 8,
            'action_to_model_type' => User::class,
            'action_to_model_id' => 12,
            'metadata' => ['name' => 'Engineering'],
        ]);

        $msg = $this->service->getMessageForAuditLog($log);
        $this->assertStringContainsString('added', $msg);
        $this->assertStringContainsString('Engineering', $msg);
        $this->assertStringContainsString('User#12', $msg);
    }

    public function test_remove_from_team_message()
    {
        $log = new AuditLog([
            'action' => Action::TEAM_REMOVE,
            'action_by_model_type' => User::class,
            'action_by_model_id' => 9,
            'action_to_model_type' => User::class,
            'action_to_model_id' => 13,
            'metadata' => ['name' => 'Design'],
        ]);

        $msg = $this->service->getMessageForAuditLog($log);
        $this->assertStringContainsString('removed', $msg);
        $this->assertStringContainsString('Design', $msg);
        $this->assertStringContainsString('User#13', $msg);
    }

    public function test_unknown_action_returns_empty_string()
    {
        $log = new AuditLog([
            'action' => 'UNKNOWN_ACTION',
            'action_by_model_type' => null,
            'action_by_model_id' => null,
            'action_to_model_type' => null,
            'action_to_model_id' => null,
            'metadata' => [],
        ]);

        $msg = $this->service->getMessageForAuditLog($log);
        $this->assertSame('', $msg);
    }

    public function test_system_actor_is_displayed_correctly()
    {
        $log = new AuditLog([
            'action' => Action::PERMISSION_CREATE,
            'action_by_model_type' => SystemActor::class,
            'action_by_model_id' => null,
            'action_to_model_type' => Permission::class,
            'action_to_model_id' => 999,
            'metadata' => ['name' => 'test-perm'],
        ]);

        $msg = $this->service->getMessageForAuditLog($log);
        $this->assertStringContainsString('System', $msg);
        $this->assertStringContainsString('test-perm', $msg);
    }
}
