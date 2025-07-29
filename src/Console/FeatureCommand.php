<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Models\Feature;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;

class FeatureCommand extends AbstractBaseEntityCommand
{
    protected $signature = 'gatekeeper:feature';

    protected $description = 'Manage features';

    public function __construct(
        ModelService $modelService,
        ModelMetadataService $modelMetadataService,
    ) {
        $this->entity = GatekeeperEntity::Feature;
        $this->entityTable = (new Feature)->getTable();

        parent::__construct($modelService, $modelMetadataService);
    }
}
