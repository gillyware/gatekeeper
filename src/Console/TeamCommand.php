<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Models\Team;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;

class TeamCommand extends AbstractBaseEntityCommand
{
    protected $signature = 'gatekeeper:team';

    protected $description = 'Manage teams';

    public function __construct(
        ModelService $modelService,
        ModelMetadataService $modelMetadataService,
    ) {
        $this->entity = GatekeeperEntity::Team;
        $this->entityTable = (new Team)->getTable();

        parent::__construct($modelService, $modelMetadataService);
    }
}
