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

        Gatekeeper::shouldReceive('assignFeatureToModel')->with($user, $feature)->once();

        $user->assignFeature($feature);
    }

    public function test_assign_features_delegates_to_facade()
    {
        $user = User::factory()->create();
        $features = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('assignAllFeaturesToModel')->with($user, $features)->once();

        $user->assignAllFeatures($features);
    }

    public function test_revoke_feature_delegates_to_facade()
    {
        $user = User::factory()->create();
        $feature = 'edit-posts';

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('revokeFeatureFromModel')->with($user, $feature)->once();

        $user->revokeFeature($feature);
    }

    public function test_revoke_features_delegates_to_facade()
    {
        $user = User::factory()->create();
        $features = ['edit-posts', 'delete-posts'];

        Gatekeeper::shouldReceive('for')->with($user)->andReturn($this->gatekeeperForModelService->setModel($user));

        Gatekeeper::shouldReceive('revokeAllFeaturesFromModel')->with($user, $features)->once();

        $user->revokeAllFeatures($features);
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
