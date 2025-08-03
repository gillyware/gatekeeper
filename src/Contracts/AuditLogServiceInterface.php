<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Packets\AuditLog\AuditLogPagePacket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditLogServiceInterface
{
    /**
     * Check if the audit log table exists.
     */
    public function tableExists(): bool;

    /**
     * Get a page of audit logs.
     */
    public function getPage(AuditLogPagePacket $packet): LengthAwarePaginator;

    /**
     * Get the message for the audit log based on the action type.
     */
    public function getMessageForAuditLog(AuditLog $log): string;
}
