<?php

namespace Gillyware\Gatekeeper\Repositories;

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
use Gillyware\Gatekeeper\Contracts\AuditLogRepositoryInterface;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Packets\AuditLog\AbstractBaseStoreAuditLogPacket;
use Gillyware\Gatekeeper\Packets\AuditLog\AuditLogPagePacket;
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
    public function create(AbstractBaseStoreAuditLogPacket $packet): AuditLog
    {
        return AuditLog::create($packet->toArray());
    }

    /**
     * Get a page of audit logs.
     */
    public function getPage(AuditLogPagePacket $packet): LengthAwarePaginator
    {
        return AuditLog::query()
            ->orderBy('created_at', $packet->createdAtOrder)
            ->paginate(10, ['*'], 'page', $packet->page);
    }
}
