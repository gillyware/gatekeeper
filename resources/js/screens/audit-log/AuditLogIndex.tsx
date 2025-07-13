import AuditLogTable from '@/components/audit-log/AuditLogTable';
import AuditLogLayout from '@/layouts/audit-log-layout';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';

export default function AuditLogIndex() {
    return (
        <GatekeeperLayout>
            <AuditLogLayout>
                <AuditLogTable />
            </AuditLogLayout>
        </GatekeeperLayout>
    );
}
