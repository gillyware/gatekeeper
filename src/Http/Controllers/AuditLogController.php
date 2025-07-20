<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Http\Requests\Audit\AuditLogPageRequest;
use Gillyware\Gatekeeper\Services\AuditLogService;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class AuditLogController extends AbstractBaseController
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    /**
     * Get a page of audit logs.
     */
    public function index(AuditLogPageRequest $request): HttpFoundationResponse
    {
        $pageNumber = $request->validated('page');
        $createdAtOrder = $request->validated('created_at_order');

        if (! $this->auditLogService->tableExists()) {
            return $this->errorResponse('The audit log table does not exist in the database.');
        }

        return Response::json($this->auditLogService->getPage($pageNumber, $createdAtOrder));
    }
}
