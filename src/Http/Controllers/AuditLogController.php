<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Http\Requests\Audit\AuditLogPageRequest;
use Gillyware\Gatekeeper\Models\AuditLog;
use Gillyware\Gatekeeper\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    /**
     * Get a page of audit logs.
     */
    public function index(AuditLogPageRequest $request): JsonResponse
    {
        $pageNumber = $request->validated('page');
        $createdAtOrder = $request->validated('created_at_order');

        if (! AuditLog::tableExists()) {
            return $this->errorResponse('The audit log table does not exist in the database.');
        }

        $paginator = AuditLog::query()
            ->orderBy('created_at', $createdAtOrder)
            ->paginate(10, ['*'], 'page', $pageNumber)
            ->through(fn (AuditLog $log) => [
                'id' => $log->id,
                'message' => $this->auditLogService->getMessageForAuditLog($log),
                'created_at' => $log->created_at->format('Y-m-d H:i:s T'),
            ]);

        return Response::json($paginator);
    }
}
