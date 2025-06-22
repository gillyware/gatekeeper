import { useApi } from '@/lib/api';
import { cn } from '@/lib/utils';
import { Pagination } from '@/types/api';
import { AuditLogPageRequest } from '@/types/api/audit';
import { AuditLog } from '@/types/models';
import { Button } from '@components/ui/button';
import { ArrowUpDown, Loader } from 'lucide-react';
import { HTMLAttributes, useEffect, useState } from 'react';

export default function AuditLogsTable({ className = '', ...props }: HTMLAttributes<HTMLDivElement>) {
    const api = useApi();

    const [auditLogs, setAuditLogs] = useState<AuditLog[]>([]);
    const [pagination, setPagination] = useState<Omit<Pagination<AuditLog>, 'data'> | null>(null);

    const [pageRequest, setPageRequest] = useState<AuditLogPageRequest>({
        page: 1,
        created_at_order: 'desc',
    });

    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchAuditLogs = async () => {
            setLoading(true);
            setError(null);

            const response = await api.getAuditLogs(pageRequest);

            if (response.status >= 400) {
                setError(response.errors?.general || 'Failed to load audit logs.');
                setPagination(null);
                setLoading(false);
                return;
            }

            const { data, ...meta } = response.data as Pagination<AuditLog>;
            setAuditLogs(data);
            setPagination(meta);
            setLoading(false);
        };

        fetchAuditLogs();
    }, [pageRequest]);

    return (
        <div className={cn('w-full', className)} {...props}>
            <div className="overflow-auto rounded-lg border dark:border-gray-700">
                <table className="w-full text-sm">
                    <thead className="bg-muted">
                        <tr>
                            <th className="px-4 py-2 text-left font-semibold">Action</th>
                            <th className="px-4 py-2 text-left font-semibold">
                                <span className="inline-flex items-center gap-1">
                                    Date/Time
                                    <ArrowUpDown
                                        className="h-4 w-4 cursor-pointer opacity-50"
                                        onClick={() =>
                                            setPageRequest((prev) => ({
                                                ...prev,
                                                page: 1,
                                                created_at_order: prev.created_at_order === 'asc' ? 'desc' : 'asc',
                                            }))
                                        }
                                    />
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {loading ? (
                            <tr>
                                <td colSpan={2} className="text-muted-foreground px-4 py-6 text-center">
                                    <div className="inline-flex items-center gap-2">
                                        <Loader className="mx-auto h-4 w-4 animate-spin text-gray-500 dark:text-gray-400" />
                                    </div>
                                </td>
                            </tr>
                        ) : error ? (
                            <tr>
                                <td colSpan={2} className="px-4 py-6 text-center text-red-500">
                                    {error}
                                </td>
                            </tr>
                        ) : auditLogs.length === 0 ? (
                            <tr>
                                <td colSpan={2} className="text-muted-foreground px-4 py-6 text-center">
                                    No audit logs found.
                                </td>
                            </tr>
                        ) : (
                            auditLogs.map((log) => (
                                <tr key={log.id} className="border-t transition-colors dark:border-gray-700">
                                    <td className="px-4 py-2" dangerouslySetInnerHTML={{ __html: log.message }} />
                                    <td className="px-4 py-2">{log.created_at}</td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>

            {pagination && pagination.total > 0 && (
                <div className="flex w-full items-center justify-end gap-2 pt-2">
                    <Button
                        size="sm"
                        variant="outline"
                        disabled={pageRequest.page === 1}
                        onClick={() => setPageRequest((prev) => ({ ...prev, page: prev.page - 1 }))}
                    >
                        Previous
                    </Button>

                    <span className="text-sm">{`${pagination.from} to ${pagination.to} of ${pagination.total}`}</span>

                    <Button
                        size="sm"
                        variant="outline"
                        disabled={pageRequest.page === pagination.last_page}
                        onClick={() => setPageRequest((prev) => ({ ...prev, page: prev.page + 1 }))}
                    >
                        Next
                    </Button>
                </div>
            )}
        </div>
    );
}
