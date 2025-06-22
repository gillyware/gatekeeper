import DeletePermission from '@/components/permissions/DeletePermission';
import { useGatekeeper } from '@/context/GatekeeperContext';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import PermissionsLayout from '@/layouts/permissions-layout';
import { useApi } from '@/lib/api';
import { Permission } from '@/types/models';
import DeactivatePermission from '@components/permissions/DeactivatePermission';
import PermissionForm from '@components/permissions/PermissionForm';
import PermissionSummary from '@components/permissions/PermissionSummary';
import ReactivatePermission from '@components/permissions/ReactivatePermission';
import { Loader } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router';

export default function ManagePermission() {
    const api = useApi();
    const { user } = useGatekeeper();
    const { permissionId } = useParams<{ permissionId: string }>();
    const navigate = useNavigate();

    const [permission, setPermission] = useState<Permission | null>(null);

    const updatePermission = (newPermission: Permission) => {
        setPermission(newPermission);
    };

    useEffect(() => {
        const fetchPermission = async () => {
            const response = await api.getPermission({ id: Number(permissionId) });

            if (response.status < 400) {
                setPermission(response.data as Permission);
            } else {
                console.error('Failed to fetch permission:', response.errors);
            }
        };

        fetchPermission();
    }, []);

    return (
        <GatekeeperLayout>
            <PermissionsLayout>
                {user.permissions.can_manage && (
                    <>
                        {permission ? (
                            <>
                                <PermissionSummary permission={permission} />

                                <div className="space-y-6">
                                    <PermissionForm action="update" permission={permission} updatePermission={updatePermission} />
                                </div>

                                {permission.is_active ? (
                                    <DeactivatePermission permission={permission} updatePermission={updatePermission} />
                                ) : (
                                    <ReactivatePermission permission={permission} updatePermission={updatePermission} />
                                )}

                                <DeletePermission permission={permission} />
                            </>
                        ) : (
                            <Loader className="h-6 w-6 animate-spin text-gray-500 dark:text-gray-400" />
                        )}
                    </>
                )}
            </PermissionsLayout>
        </GatekeeperLayout>
    );
}
