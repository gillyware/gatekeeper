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
        $key = "permissions.{$this->model->getMorphClass()}.{$this->model->getKey()}";
        $collection = collect(['perm']);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getModelPermissionNames($this->model));

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with($key, $collection);

        $this->service->putModelPermissionNames($this->model, $collection);

        $this->cacheRepository->expects($this->once())
            ->method('forget')
            ->with($key);

        $this->service->invalidateCacheForModelPermissionNames($this->model);
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
        $key = "roles.{$this->model->getMorphClass()}.{$this->model->getKey()}";
        $collection = collect(['role']);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getModelRoleNames($this->model));

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with($key, $collection);

        $this->service->putModelRoleNames($this->model, $collection);

        $this->cacheRepository->expects($this->once())
            ->method('forget')
            ->with($key);

        $this->service->invalidateCacheForModelRoleNames($this->model);
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
        $key = "teams.{$this->model->getMorphClass()}.{$this->model->getKey()}";
        $collection = collect(['team']);

        $this->cacheRepository->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($collection);

        $this->assertSame($collection, $this->service->getModelTeamNames($this->model));

        $this->cacheRepository->expects($this->once())
            ->method('put')
            ->with($key, $collection);

        $this->service->putModelTeamNames($this->model, $collection);

        $this->cacheRepository->expects($this->once())
            ->method('forget')
            ->with($key);

        $this->service->invalidateCacheForModelTeamNames($this->model);
    }
}
