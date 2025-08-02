<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Services;

use Gillyware\Gatekeeper\Repositories\CacheRepository;
use Gillyware\Gatekeeper\Services\CacheService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
        $this->service = new CacheService($cacheRepository);
        $this->model = new User(['id' => 1]);
    }

    public function test_clear_delegates_to_repository(): void
    {
        $this->cacheRepository->expects($this->once())->method('clear');
        $this->service->clear();
    }

    public function test_get_all_permissions(): void
    {
        $collection = collect(['foo']);
        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with('permissions')
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getAllPermissions());
    }

    public function test_put_all_permissions(): void
    {
        $collection = collect(['bar']);
        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with('permissions', $collection);

        $this->service->putAllPermissions($collection);
    }

    public function test_model_permission_cache(): void
    {
        $key = "permissions.{$this->model->getMorphClass()}.{$this->model->getKey()}.links";
        $collection = collect(['perm']);

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

        $this->service->invalidateCacheForModelPermissionLinksAndAccess($this->model);
    }

    public function test_all_roles_cache(): void
    {
        $collection = collect(['role']);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with('roles')
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getAllRoles());

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with('roles', $collection);

        $this->service->putAllRoles($collection);

        $this->cacheRepository->expects($this->once())
            ->method('forget')
            ->with('roles');

        $this->service->invalidateCacheForAllRoles();
    }

    public function test_model_roles_cache(): void
    {
        $key = "roles.{$this->model->getMorphClass()}.{$this->model->getKey()}.links";
        $collection = collect(['role']);

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

        $this->service->invalidateCacheForModelRoleLinksAndAccess($this->model);
    }

    public function test_all_features_cache(): void
    {
        $collection = collect(['feature']);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with('features')
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getAllFeatures());

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with('features', $collection);

        $this->service->putAllFeatures($collection);

        $this->cacheRepository->expects($this->once())
            ->method('forget')
            ->with('features');

        $this->service->invalidateCacheForAllFeatures();
    }

    public function test_model_features_cache(): void
    {
        $key = "features.{$this->model->getMorphClass()}.{$this->model->getKey()}.links";
        $collection = collect(['feature']);

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

        $this->service->invalidateCacheForModelFeatureLinksAndAccess($this->model);
    }

    public function test_all_teams_cache(): void
    {
        $collection = collect(['team']);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with('teams')
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getAllTeams());

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with('teams', $collection);

        $this->service->putAllTeams($collection);

        $this->cacheRepository->expects($this->once())
            ->method('forget')
            ->with('teams');

        $this->service->invalidateCacheForAllTeams();
    }

    public function test_model_teams_cache(): void
    {
        $key = "teams.{$this->model->getMorphClass()}.{$this->model->getKey()}.links";
        $collection = collect(['team']);

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

        $this->service->invalidateCacheForModelTeamLinksAndAccess($this->model);
    }
}
