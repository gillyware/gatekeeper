<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Models\ModelHasPermission;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Repositories\ModelHasPermissionRepository;
use Gillyware\Gatekeeper\Services\CacheService;
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

        $this->app->forgetInstance(CacheService::class);
        $this->app->forgetInstance(ModelHasPermissionRepository::class);

        $cacheMock = $this->createMock(CacheService::class);
        $this->app->singleton(CacheService::class, fn () => $cacheMock);

        $this->cacheService = $cacheMock;
        $this->repository = $this->app->make(ModelHasPermissionRepository::class);
    }

    public function test_it_can_check_if_a_permission_is_assigned_to_any_model()
    {
        $permission = Permission::factory()->create();

        $this->assertFalse($this->repository->existsForEntity($permission));

        $user = User::factory()->create();
        $this->repository->assignToModel($user, $permission);

        $this->assertTrue($this->repository->existsForEntity($permission));
    }

    public function test_it_can_create_model_permission_record()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('invalidateCacheForModelPermissionLinks')
            ->with($user);

        $record = $this->repository->assignToModel($user, $permission);

        $this->assertInstanceOf(ModelHasPermission::class, $record);
        $this->assertDatabaseHas(Config::get('gatekeeper.tables.model_has_permissions', GatekeeperConfigDefault::TABLES_MODEL_HAS_PERMISSIONS), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'permission_id' => $permission->id,
        ]);
    }

    public function test_it_can_soft_delete_model_permission()
    {
        $user = User::factory()->create();
        $permission = Permission::factory()->create();

        $this->cacheService->expects($this->exactly(2))
            ->method('invalidateCacheForModelPermissionLinks')
            ->with($user);

        $this->repository->assignToModel($user, $permission);

        $this->assertTrue($this->repository->unassignFromModel($user, $permission));

        $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_permissions', GatekeeperConfigDefault::TABLES_MODEL_HAS_PERMISSIONS), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'permission_id' => $permission->id,
        ]);
    }
}
