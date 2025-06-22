import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import RolesLayout from '@/layouts/roles-layout';
import RolesTable from '@components/roles/RolesTable';
import HeadingSmall from '@components/ui/heading-small';

export default function RolesIndex() {
    return (
        <GatekeeperLayout>
            <RolesLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Roles Index" description="Take stock of your application's roles" />
                    <RolesTable />
                </div>
            </RolesLayout>
        </GatekeeperLayout>
    );
}
