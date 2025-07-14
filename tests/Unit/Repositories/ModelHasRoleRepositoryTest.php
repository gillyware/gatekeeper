<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Models\ModelHasRole;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Repositories\ModelHasRoleRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\MockObject\MockObject;

class ModelHasRoleRepositoryTest extends TestCase
{
    protected ModelHasRoleRepository $repository;

    protected MockObject $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->forgetInstance(CacheService::class);
        $this->app->forgetInstance(ModelHasRoleRepository::class);

        $cacheMock = $this->createMock(CacheService::class);
        $this->app->singleton(CacheService::class, fn () => $cacheMock);

        $this->cacheService = $cacheMock;
        $this->repository = $this->app->make(ModelHasRoleRepository::class);
    }

    public function test_it_can_check_if_a_role_is_assigned_to_any_model()
    {
        $role = Role::factory()->create();

        $this->assertFalse($this->repository->existsForRole($role));

        $user = User::factory()->create();
        $this->repository->create($user, $role);

        $this->assertTrue($this->repository->existsForRole($role));
    }

    public function test_it_can_create_model_role_record()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->cacheService->expects($this->once())
            ->method('invalidateCacheForModelRoleNames')
            ->with($user);

        $record = $this->repository->create($user, $role);

        $this->assertInstanceOf(ModelHasRole::class, $record);
        $this->assertDatabaseHas(Config::get('gatekeeper.tables.model_has_roles', GatekeeperConfigDefault::TABLES_MODEL_HAS_ROLES), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_it_can_soft_delete_model_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $this->cacheService->expects($this->exactly(2))
            ->method('invalidateCacheForModelRoleNames')
            ->with($user);

        $this->repository->create($user, $role);

        $this->assertTrue($this->repository->deleteForModelAndRole($user, $role));

        $this->assertSoftDeleted(Config::get('gatekeeper.tables.model_has_roles', GatekeeperConfigDefault::TABLES_MODEL_HAS_ROLES), [
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }
}
