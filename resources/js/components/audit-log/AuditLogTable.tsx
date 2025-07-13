import { useApi } from '@/lib/api';
import { getAuditLog } from '@/lib/audit-log';
import { type AuditLogTableText, auditLogText } from '@/lib/lang/en/audit-log';
import { swapOrder } from '@/lib/utils';
import { type Pagination } from '@/types/api';
import { type AuditLogPageRequest } from '@/types/api/audit';
import { type AuditLog } from '@/types/models';
import { Button } from '@components/ui/button';
import { ArrowUpDown, Loader } from 'lucide-react';
import { type SetStateAction, useEffect, useMemo, useState } from 'react';

interface AuditLogTableHeaderProps {
    language: AuditLogTableText;
    setPageRequest: (request: SetStateAction<AuditLogPageRequest>) => void;
}

interface AuditLogTablePaginationProps {
    logs: Pagination<AuditLog> | null;
    language: AuditLogTableText;
    pageRequest: AuditLogPageRequest;
    setPageRequest: (request: SetStateAction<AuditLogPageRequest>) => void;
}

export default function AuditLogTable() {
    const api = useApi();

    const [logs, setLogs] = useState<Pagination<AuditLog> | null>(null);
    const [pageRequest, setPageRequest] = useState<AuditLogPageRequest>({
        page: 1,
        created_at_order: 'desc',
    });

    const [loadingLogs, setLoadingLogs] = useState<boolean>(true);
    const [errorLoadingLogs, setErrorLoadingLogs] = useState<string | null>(null);

    const language: AuditLogTableText = useMemo(() => auditLogText.table, []);

    useEffect(() => {
        getAuditLog(api, pageRequest, setLogs, setLoadingLogs, setErrorLoadingLogs);
    }, [pageRequest]);

    return (
        <div className="w-full">
            <div className="overflow-auto rounded-lg border dark:border-gray-700">
                <table className="w-full text-sm">
                    <AuditLogTableHeader language={language} setPageRequest={setPageRequest} />
                    <tbody>
                        {loadingLogs ? (
                            <tr>
                                <td colSpan={2} className="text-muted-foreground px-4 py-6 text-center">
                                    <div className="inline-flex items-center gap-2">
                                        <Loader className="mx-auto h-4 w-4 animate-spin text-gray-500 dark:text-gray-400" />
                                    </div>
                                </td>
                            </tr>
                        ) : errorLoadingLogs ? (
                            <tr>
                                <td colSpan={2} className="px-4 py-6 text-center text-red-500">
                                    {errorLoadingLogs}
                                </td>
                            </tr>
                        ) : (logs?.data?.length || 0) === 0 ? (
                            <tr>
                                <td colSpan={2} className="text-muted-foreground px-4 py-6 text-center">
                                    {language.empty}
                                </td>
                            </tr>
                        ) : (
                            logs?.data.map((log) => (
                                <tr key={log.id} className="border-t transition-colors dark:border-gray-700">
                                    <td className="px-4 py-2" dangerouslySetInnerHTML={{ __html: log.message }} />
                                    <td className="px-4 py-2">{log.created_at}</td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            <AuditLogTablePagination logs={logs} language={language} pageRequest={pageRequest} setPageRequest={setPageRequest} />
        </div>
    );
}

function AuditLogTableHeader({ language, setPageRequest }: AuditLogTableHeaderProps) {
    return (
        <thead className="bg-muted">
            <tr>
                <th className="px-4 py-2 text-left font-semibold">{language.actionColumn}</th>
                <th className="px-4 py-2 text-left font-semibold">
                    <span className="inline-flex items-center gap-1">
                        {language.dateTimeColumn}
                        <ArrowUpDown
                            className="h-4 w-4 cursor-pointer opacity-50"
                            onClick={() =>
                                setPageRequest((prev) => ({
                                    ...prev,
                                    page: 1,
                                    created_at_order: swapOrder(prev.created_at_order),
                                }))
                            }
                        />
                    </span>
                </th>
            </tr>
        </thead>
    );
}

function AuditLogTablePagination({ logs, language, pageRequest, setPageRequest }: AuditLogTablePaginationProps) {
    if (!logs || logs.total === 0) {
        return null;
    }

    return (
        <div className="flex w-full items-center justify-end gap-2 pt-2">
            <Button
                size="sm"
                variant="outline"
                disabled={pageRequest.page === 1}
                onClick={() => setPageRequest((prev) => ({ ...prev, page: prev.page - 1 }))}
            >
                {language.previous}
            </Button>

            <span className="text-sm">{language.pagination(logs.from, logs.to, logs.total)}</span>

            <Button
                size="sm"
                variant="outline"
                disabled={pageRequest.page === logs.last_page}
                onClick={() => setPageRequest((prev) => ({ ...prev, page: prev.page + 1 }))}
            >
                {language.next}
            </Button>
        </div>
    );
}
