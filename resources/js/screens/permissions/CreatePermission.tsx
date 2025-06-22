import { useGatekeeper } from '@/context/GatekeeperContext';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import PermissionsLayout from '@/layouts/permissions-layout';
import PermissionForm from '@components/permissions/PermissionForm';
import HeadingSmall from '@components/ui/heading-small';

export default function CreatePermission() {
    const { user } = useGatekeeper();

    return (
        <GatekeeperLayout>
            <PermissionsLayout>
                {user.permissions.can_manage && (
                    <div className="space-y-6">
                        <HeadingSmall title="Create Permission" description="Introduce a new permission into your application" />
                        <PermissionForm action={'create'} />
                    </div>
                )}
            </PermissionsLayout>
        </GatekeeperLayout>
    );
}
