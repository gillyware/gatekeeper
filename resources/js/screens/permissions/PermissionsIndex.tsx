import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import PermissionsLayout from '@/layouts/permissions-layout';
import PermissionsTable from '@components/permissions/PermissionsTable';
import HeadingSmall from '@components/ui/heading-small';

export default function PermissionsIndex() {
    return (
        <GatekeeperLayout>
            <PermissionsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Permissions Index" description="Take stock of your application's permissions" />
                    <PermissionsTable />
                </div>
            </PermissionsLayout>
        </GatekeeperLayout>
    );
}
