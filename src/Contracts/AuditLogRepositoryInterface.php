<?php

namespace Gillyware\Gatekeeper\Contracts;

use Gillyware\Gatekeeper\Dtos\AuditLog\AbstractAuditLogDto;
use Gillyware\Gatekeeper\Models\AuditLog;
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
    public function create(AbstractAuditLogDto $dto): AuditLog;

    /**
     * Get a page of audit logs.
     */
    public function getPage(int $pageNumber, string $createdAtOrder): LengthAwarePaginator;
}
