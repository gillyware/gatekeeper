import { useApi } from '@/lib/api';
import { GatekeeperErrors, type Pagination } from '@/types/api';
import { type AuditLogPageRequest } from '@/types/api/audit';
import { type AuditLog } from '@/types/models';

export async function getAuditLog(
    api: ReturnType<typeof useApi>,
    request: AuditLogPageRequest,
    setLogs: (paginator: Pagination<AuditLog> | null) => void,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setLoading(true);
    setError(null);

    const response = await api.getAuditLog(request);

    if (response.status >= 400) {
        const errors: GatekeeperErrors = response.errors as GatekeeperErrors;
        setError(errors['general'] || 'Failed to fetch.');
        setLogs(null);
        setLoading(false);
        return;
    }

    const logs = response.data as Pagination<AuditLog>;
    setLogs(logs);
    setLoading(false);
}
