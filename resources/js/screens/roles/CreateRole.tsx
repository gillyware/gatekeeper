import { useGatekeeper } from '@/context/GatekeeperContext';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import RolesLayout from '@/layouts/roles-layout';
import RoleForm from '@components/roles/RoleForm';
import HeadingSmall from '@components/ui/heading-small';

export default function CreateRole() {
    const { user } = useGatekeeper();

    return (
        <GatekeeperLayout>
            <RolesLayout>
                {user.permissions.can_manage && (
                    <div className="space-y-6">
                        <HeadingSmall title="Create Role" description="Introduce a new role into your application" />
                        <RoleForm action={'create'} />
                    </div>
                )}
            </RolesLayout>
        </GatekeeperLayout>
    );
}
