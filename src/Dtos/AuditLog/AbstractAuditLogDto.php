<?php

namespace Gillyware\Gatekeeper\Dtos\AuditLog;

use Gillyware\Gatekeeper\Facades\Gatekeeper;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractAuditLogDto
{
    public Model $actionByModel;

    public ?Model $actionToModel = null;

    public ?array $metadata = null;

    public function __construct()
    {
        $this->actionByModel = Gatekeeper::getActor();
    }

    abstract public function getAction(): string;
}
