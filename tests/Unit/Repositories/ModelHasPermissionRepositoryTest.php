<?php

namespace Braxey\Gatekeeper\Tests\Unit\Repositories;

use Braxey\Gatekeeper\Models\ModelHasPermission;
use Braxey\Gatekeeper\Models\Permission;
use Braxey\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Braxey\Gatekeeper\Tests\Fixtures\User;
use Braxey\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class ModelHasPermissionRepositoryTest extends TestCase
{
    protected ModelHasPermissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ModelHasPermissionRepository::class);
    }

    public function test_it_can_create_model_permission_record()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $record = $this->repository->create($user, $permission);

        $this->assertInstanceOf(ModelHasPermission::class, $record);
        $this->assertDatabaseHas(Config::get('gatekeeper.tables.model_has_permissions'), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'permission_id' => $permission->id,
        ]);
    }

    public function test_it_can_get_model_permission_records()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->repository->create($user, $permission);

        $records = $this->repository->getForModelAndPermission($user, $permission);

        $this->assertCount(1, $records);
        $this->assertInstanceOf(ModelHasPermission::class, $records->first());
    }

    public function test_it_can_get_most_recent_model_permission_including_trashed()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->repository->create($user, $permission);
        $record = $this->repository->getRecentForModelAndPermissionIncludingTrashed($user, $permission);

        $this->assertInstanceOf(ModelHasPermission::class, $record);
    }

    public function test_it_can_soft_delete_model_permission()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->repository->create($user, $permission);

        $this->assertTrue($this->repository->deleteForModelAndPermission($user, $permission));

        $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_permissions'), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'permission_id' => $permission->id,
        ]);
    }
}
