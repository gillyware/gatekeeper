<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Dtos\AuditLog\AbstractAuditLogDto;
use Gillyware\Gatekeeper\Models\AuditLog;

class AuditLogRepository
{
    /**
     * Create a new audit log entry.
     */
    public function create(AbstractAuditLogDto $dto): AuditLog
    {
        return AuditLog::create([
            'action' => $dto->getAction(),
            'action_by_model_type' => $dto->actionByModel->getMorphClass(),
            'action_by_model_id' => $dto->actionByModel->getKey(),
            'action_to_model_type' => $dto->actionToModel?->getMorphClass(),
            'action_to_model_id' => $dto->actionToModel?->getKey(),
            'metadata' => $dto->metadata,
        ]);
    }
}
