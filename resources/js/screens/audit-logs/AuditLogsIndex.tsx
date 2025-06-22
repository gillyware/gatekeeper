import AuditLogsLayout from '@/layouts/audit-layout';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import AuditLogsTable from '@components/audit-logs/AuditLogTable';

export default function AuditLogsIndex() {
    return (
        <GatekeeperLayout>
            <AuditLogsLayout>
                <AuditLogsTable />
            </AuditLogsLayout>
        </GatekeeperLayout>
    );
}
