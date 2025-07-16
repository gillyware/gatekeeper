<?php

namespace Gillyware\Gatekeeper\Tests\Feature\Controllers;

use Gillyware\Gatekeeper\Database\Seeders\GatekeeperPermissionsSeeder;
use Gillyware\Gatekeeper\Enums\GatekeeperPermissionName;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(GatekeeperPermissionsSeeder::class);
        $this->user = User::factory()->create();
        $this->be($this->user);
    }

    public function test_user_with_permission_can_access_the_gatekeeper_dashboard()
    {
        $this->user->assignPermission(GatekeeperPermissionName::View);

        $this->get(route('gatekeeper.home'))
            ->assertStatus(Response::HTTP_OK)
            ->assertViewIs('gatekeeper::layout');
    }

    public function test_user_without_permission_cannot_access_the_gatekeeper_dashboard()
    {
        $this->get(route('gatekeeper.home'))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
