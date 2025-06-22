<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Models\ModelHasPermission;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\MockObject\MockObject;

class ModelHasPermissionRepositoryTest extends TestCase
{
    protected ModelHasPermissionRepository $repository;

    protected MockObject $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $cacheMock = $this->createMock(CacheService::class);
        $this->cacheService = $cacheMock;

        $this->repository = new ModelHasPermissionRepository($cacheMock, app()->make(ModelMetadataService::class));
    }

    public function test_it_can_check_if_a_permission_is_assigned_to_any_model()
    {
        $permission = Permission::factory()->create();

        $this->assertFalse($this->repository->existsForPermission($permission));

        $user = User::factory()->create();
        $this->repository->create($user, $permission);

        $this->assertTrue($this->repository->existsForPermission($permission));
    }

    public function test_it_can_create_model_permission_record()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('invalidateCacheForModelPermissionNames')
            ->with($user);

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

        $this->cacheService->expects($this->exactly(2))
            ->method('invalidateCacheForModelPermissionNames')
            ->with($user);

        $this->repository->create($user, $permission);

        $this->assertTrue($this->repository->deleteForModelAndPermission($user, $permission));

        $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_permissions'), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'permission_id' => $permission->id,
        ]);
    }
}
