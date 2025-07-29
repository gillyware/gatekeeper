<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Models\Role;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;

class RoleCommand extends AbstractBaseEntityCommand
{
    protected $signature = 'gatekeeper:role';

    protected $description = 'Manage roles';

    public function __construct(
        ModelService $modelService,
        ModelMetadataService $modelMetadataService,
    ) {
        $this->entity = GatekeeperEntity::Role;
        $this->entityTable = (new Role)->getTable();

        parent::__construct($modelService, $modelMetadataService);
    }
}
