<?php

namespace Gillyware\Gatekeeper\Packets\AuditLog;

use Gillyware\Gatekeeper\Enums\AuditLogAction;
use Gillyware\Gatekeeper\Models\AbstractBaseEntityModel;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Repositories\AuditLogRepository;
use Illuminate\Database\Eloquent\Model;

class StoreAuditLogPacketBuilder
{
    private ?AuditLogAction $action = null;

    private ?Model $actionTo = null;

    private ?array $metadata = null;

    public static function action(AuditLogAction $action): static
    {
        return (new StoreAuditLogPacketBuilder)->setAction($action);
    }

    public function setAction(AuditLogAction $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function setActionToEntity(AbstractBaseEntityModel $entityModel): static
    {
        return $this->setActionToModel($entityModel)->pushMetadata('name', $entityModel->name);
    }

    public function setActionToModel(Model $model): static
    {
        $this->actionTo = $model;

        return $this;
    }

    public function pushMetadata(string $key, mixed $value): static
    {
        if ($this->metadata === null) {
            $this->metadata = [];
        }

        $this->metadata[$key] = $value;

        return $this;
    }

    public function build(): StoreAuditLogPacket
    {
        return StoreAuditLogPacket::from([
            'action' => $this->action,
            'action_to' => $this->actionTo,
            'metadata' => $this->metadata,
        ]);
    }

    public function store(): AuditLog
    {
        return resolve(AuditLogRepository::class)->create($this->build());
    }
}
