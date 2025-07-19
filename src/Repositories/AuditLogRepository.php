<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\AuditLogRepositoryInterface;
use Gillyware\Gatekeeper\Dtos\AuditLog\AbstractAuditLogDto;
use Gillyware\Gatekeeper\Models\AuditLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    /**
     * Check if the audit log table exists.
     */
    public function tableExists(): bool
    {
        return Schema::hasTable(Config::get('gatekeeper.tables.audit_log', GatekeeperConfigDefault::TABLES_AUDIT_LOG));
    }

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

    /**
     * Get a page of audit logs.
     */
    public function getPage(int $pageNumber, string $createdAtOrder): LengthAwarePaginator
    {
        return AuditLog::query()
            ->orderBy('created_at', $createdAtOrder)
            ->paginate(10, ['*'], 'page', $pageNumber);
    }
}
