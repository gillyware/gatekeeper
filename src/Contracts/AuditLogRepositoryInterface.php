<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Packets\AuditLogPagePacket;
use Gillyware\Gatekeeper\Packets\StoreAuditLogPacket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditLogRepositoryInterface
{
    /**
     * Check if the audit log table exists.
     */
    public function tableExists(): bool;

    /**
     * Create a new audit log entry.
     */
    public function create(StoreAuditLogPacket $packet): AuditLog;

    /**
     * Get a page of audit logs.
     */
    public function getPage(AuditLogPagePacket $packet): LengthAwarePaginator;
}
