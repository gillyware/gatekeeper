<?php

namespace Gillyware\Gatekeeper\Console;

use Gillyware\Gatekeeper\Enums\GatekeeperEntity;
use Gillyware\Gatekeeper\Models\Permission;
use Gillyware\Gatekeeper\Services\ModelMetadataService;
use Gillyware\Gatekeeper\Services\ModelService;

class PermissionCommand extends AbstractBaseEntityCommand
{
    protected $signature = 'gatekeeper:permission';

    protected $description = 'Manage permissions';

    public function __construct(
        ModelService $modelService,
        ModelMetadataService $modelMetadataService,
    ) {
        $this->entity = GatekeeperEntity::Permission;
        $this->entityTable = (new Permission)->getTable();

        parent::__construct($modelService, $modelMetadataService);
    }
}
