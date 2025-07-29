<?php

namespace Gillyware\Gatekeeper\Tests\Unit\Traits;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Gillyware\Gatekeeper\Services\GatekeeperForModelService;
use Gillyware\Gatekeeper\Tests\Fixtures\User;
use Gillyware\Gatekeeper\Tests\TestCase;

class HasFeaturesTest extends TestCase
{
    private GatekeeperForModelService $gatekeeperForModelService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gatekeeperForModelService = app(GatekeeperForModelService::class);
    }

    public function test_assign_feature_delegates_to_facade()
    {
        $user = User::factory()->create();
        $feature = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('assignFeatureForModel')->with($user, $feature)->once();

        $user->assignFeature($feature);
    }

    public function test_assign_features_delegates_to_facade()
    {
        $user = User::factory()->create();
        $features = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('assignAllFeaturesForModel')->with($user, $features)->once();

        $user->assignAllFeatures($features);
    }

    public function test_unassign_feature_delegates_to_facade()
    {
        $user = User::factory()->create();
        $feature = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('unassignFeatureForModel')->with($user, $feature)->once();

        $user->unassignFeature($feature);
    }

    public function test_unassign_features_delegates_to_facade()
    {
        $user = User::factory()->create();
        $features = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('denyAllFeaturesFromModel')->with($user, $features)->once();

        $user->denyAllFeatures($features);
    }

    public function test_deny_feature_delegates_to_facade()
    {
        $user = User::factory()->create();
        $feature = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('denyFeatureFromModel')->with($user, $feature)->once();

        $user->denyFeature($feature);
    }

    public function test_deny_features_delegates_to_facade()
    {
        $user = User::factory()->create();
        $features = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('unassignAllFeaturesForModel')->with($user, $features)->once();

        $user->unassignAllFeatures($features);
    }

    public function test_has_feature_delegates_to_facade()
    {
        $user = User::factory()->create();
        $feature = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('modelHasFeature')->with($user, $feature)->once();

        $user->hasFeature($feature);
    }

    public function test_has_any_feature_delegates_to_facade()
    {
        $user = User::factory()->create();
        $features = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('modelHasAnyFeature')->with($user, $features)->once();

        $user->hasAnyFeature($features);
    }

    public function test_has_all_features_delegates_to_facade()
    {
        $user = User::factory()->create();
        $features = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('modelHasAllFeatures')->with($user, $features)->once();

        $user->hasAllFeatures($features);
    }
}
