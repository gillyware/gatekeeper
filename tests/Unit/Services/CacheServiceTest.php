<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Services;

use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Repositories\CacheRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Support\AccessCachePrimer;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class CacheServiceTest extends TestCase
{
    protected MockObject $cacheRepository;

    protected CacheService $service;

    protected User $model;

    protected function setUp(): void
    {
        parent::setUp();

        $cacheRepository = $this->createMock(CacheRepository::class);
        $this->cacheRepository = $cacheRepository;

        $this->service = new CacheService($cacheRepository, new AccessCachePrimer);

        $this->model = new User(['id' => 1]);
    }

    public function test_clear_delegates_to_repository(): void
    {
        $this->cacheRepository->expects($this->once())->method('clear');
        $this->service->clear();
    }

    public function test_all_permissions_cache(): void
    {
        $permission = Permission::factory()->create();
        $collection = collect([$permission->name]);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with('permissions')
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getAllPermissions());

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with('permissions', $collection);

        $this->service->putAllPermissions($collection);

        $this->cacheRepository->expects($this->once())->method('clear');

        $this->service->invalidateCacheForModel($permission);
    }

    public function test_model_permission_cache(): void
    {
        $key = "permissions.{$this->model->getMorphClass()}.{$this->model->getKey()}.links";

        $permission = Permission::factory()->create();

        $collection = collect([$permission->name => [
            'permission' => $permission,
            'denied' => false,
        ]]);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getModelPermissionLinks($this->model));

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with($key, $collection);

        $this->service->putModelPermissionLinks($this->model, $collection);

        $this->cacheRepository->expects($this->atLeastOnce())->method('forget');

        $this->service->invalidateCacheForModel($this->model);
    }

    public function test_all_roles_cache(): void
    {
        $role = Role::factory()->create();
        $collection = collect([$role->name]);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with('roles')
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getAllRoles());

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with('roles', $collection);

        $this->service->putAllRoles($collection);

        $this->cacheRepository->expects($this->once())->method('clear');

        $this->service->invalidateCacheForModel($role);
    }

    public function test_model_roles_cache(): void
    {
        $key = "roles.{$this->model->getMorphClass()}.{$this->model->getKey()}.links";

        $role = Role::factory()->create();

        $collection = collect([$role->name => [
            'role' => $role,
            'denied' => false,
        ]]);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getModelRoleLinks($this->model));

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with($key, $collection);

        $this->service->putModelRoleLinks($this->model, $collection);

        $this->cacheRepository->expects($this->atLeastOnce())->method('forget');

        $this->service->invalidateCacheForModel($this->model);
    }

    public function test_all_features_cache(): void
    {
        $feature = Feature::factory()->create();
        $collection = collect([$feature->name]);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with('features')
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getAllFeatures());

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with('features', $collection);

        $this->service->putAllFeatures($collection);

        $this->cacheRepository->expects($this->once())->method('clear');

        $this->service->invalidateCacheForModel($feature);
    }

    public function test_model_features_cache(): void
    {
        $key = "features.{$this->model->getMorphClass()}.{$this->model->getKey()}.links";

        $feature = Feature::factory()->create();

        $collection = collect([$feature->name => [
            'feature' => $feature,
            'denied' => false,
        ]]);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getModelFeatureLinks($this->model));

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with($key, $collection);

        $this->service->putModelFeatureLinks($this->model, $collection);

        $this->cacheRepository->expects($this->atLeastOnce())->method('forget');

        $this->service->invalidateCacheForModel($this->model);
    }

    public function test_all_teams_cache(): void
    {
        $team = Team::factory()->create();
        $collection = collect([$team->name]);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with('teams')
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getAllTeams());

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with('teams', $collection);

        $this->service->putAllTeams($collection);

        $this->cacheRepository->expects($this->once())->method('clear');

        $this->service->invalidateCacheForModel($team);
    }

    public function test_model_teams_cache(): void
    {
        $key = "teams.{$this->model->getMorphClass()}.{$this->model->getKey()}.links";

        $team = Team::factory()->create();

        $collection = collect([$team->name => [
            'team' => $team,
            'denied' => false,
        ]]);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getModelTeamLinks($this->model));

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with($key, $collection);

        $this->service->putModelTeamLinks($this->model, $collection);

        $this->cacheRepository->expects($this->atLeastOnce())->method('forget');

        $this->service->invalidateCacheForModel($this->model);
    }
}
