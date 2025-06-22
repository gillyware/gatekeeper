import DeleteRole from '@/components/roles/DeleteRole';
import { useGatekeeper } from '@/context/GatekeeperContext';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import RolesLayout from '@/layouts/roles-layout';
import { useApi } from '@/lib/api';
import { Role } from '@/types/models';
import DeactivateRole from '@components/roles/DeactivateRole';
import ReactivateRole from '@components/roles/ReactivateRole';
import RoleForm from '@components/roles/RoleForm';
import RoleSummary from '@components/roles/RoleSummary';
import { Loader } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useParams } from 'react-router';

export default function ManageRole() {
    const api = useApi();
    const { user } = useGatekeeper();
    const { roleId } = useParams<{ roleId: string }>();

    const [role, setRole] = useState<Role | null>(null);

    const updateRole = (newRole: Role) => {
        setRole(newRole);
    };

    useEffect(() => {
        const fetchRole = async () => {
            const response = await api.getRole({ id: Number(roleId) });

            if (response.status < 400) {
                setRole(response.data as Role);
            } else {
                console.error('Failed to fetch role:', response.errors);
            }
        };

        fetchRole();
    }, []);

    return (
        <GatekeeperLayout>
            <RolesLayout>
                {user.permissions.can_manage && (
                    <>
                        {role ? (
                            <>
                                <RoleSummary role={role} />

                                <div className="space-y-6">
                                    <RoleForm action="update" role={role} updateRole={updateRole} />
                                </div>

                                {role.is_active ? (
                                    <DeactivateRole role={role} updateRole={updateRole} />
                                ) : (
                                    <ReactivateRole role={role} updateRole={updateRole} />
                                )}

                                <DeleteRole role={role} />
                            </>
                        ) : (
                            <Loader className="h-6 w-6 animate-spin text-gray-500 dark:text-gray-400" />
                        )}
                    </>
                )}
            </RolesLayout>
        </GatekeeperLayout>
    );
}
