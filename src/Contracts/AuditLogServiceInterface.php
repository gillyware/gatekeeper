<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Models\AuditLog;
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
    public function getPage(int $pageNumber, string $createdAtOrder): LengthAwarePaginator;

    /**
     * Get the message for the audit log based on the action type.
     */
    public function getMessageForAuditLog(AuditLog $log): string;
}
