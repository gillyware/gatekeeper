<?php

namespace Gillyware\Gatekeeper\Tests\Feature\Controllers;

use Gillyware\Gatekeeper\Database\Seeders\GatekeeperPermissionsSeeder;
use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Enums\GatekeeperPermission;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Repositories\CacheRepository;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class ModelControllerTest extends TestCase
{
    private User $user;

    private CacheRepository $cacheRepository;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('gatekeeper.models.manageable', [
            [
                'label' => 'User',
                'class' => User::class,
                'searchable' => [
                    ['column' => 'id', 'label' => 'ID'],
                    ['column' => 'name', 'label' => 'name'],
                    ['column' => 'email', 'label' => 'email'],
                ],
                'displayable' => [
                    ['column' => 'id', 'label' => 'ID'],
                    ['column' => 'name', 'label' => 'Name'],
                    ['column' => 'email', 'label' => 'Email'],
                ],
            ],
        ]);

        $this->seed(GatekeeperPermissionsSeeder::class);
        $this->user = User::factory()->create();
        $this->be($this->user);
        $this->cacheRepository = app()->make(CacheRepository::class);
    }

    public function test_lookup_model_with_access()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);

        $this->getJson(route('gatekeeper.api.models.show', [
            'modelLabel' => 'User',
            'modelPk' => $this->user->getKey(),
        ]))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['model_label' => 'User']);
    }

    public function test_assign_and_revoke_permission_to_model()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);

        $target = User::factory()->create();
        $permission = Permission::factory()->create();
        $this->cacheRepository->clear();

        $this->postJson(route('gatekeeper.api.models.assign', [
            'modelLabel' => 'User',
            'modelPk' => (string) $target->getKey(),
            'entity' => GatekeeperEntity::Permission->value,
        ]), [
            'entity_name' => $permission->name,
        ])->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['message' => 'Permission assigned successfully']);

        $this->deleteJson(route('gatekeeper.api.models.revoke', [
            'modelLabel' => 'User',
            'modelPk' => (string) $target->getKey(),
            'entity' => GatekeeperEntity::Permission->value,
        ]), [
            'entity_name' => $permission->name,
        ])->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['message' => 'Permission revoked successfully']);
    }

    public function test_model_lookup_fails_for_missing_model()
    {
        $this->user->assignAllPermissions([GatekeeperPermission::View, GatekeeperPermission::Manage]);

        [$pk, $className] = [999999, User::class];

        $this->getJson(route('gatekeeper.api.models.show', [
            'modelLabel' => 'User',
            'modelPk' => (string) $pk,
        ]))->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonFragment(['message' => "Model with primary key '{$pk}' not found in class '{$className}'."]);
    }

    public function test_protected_routes_fail_without_permission()
    {
        $this->getJson(route('gatekeeper.api.models.show', [
            'modelLabel' => 'User',
            'modelPk' => fake()->word(),
        ]))->assertStatus(Response::HTTP_BAD_REQUEST);
    }
}
