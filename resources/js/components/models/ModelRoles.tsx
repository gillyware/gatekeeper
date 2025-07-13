import { DebouncedInput } from '@/components/ui/debounced-input';
import { useGatekeeper } from '@/context/GatekeeperContext';
import { useIsMobile } from '@/hooks/use-mobile';
import { useApi } from '@/lib/api';
import { assignEntityToModel, fetchEntityAssignmentsForModel, fetchUnassignedEntitiesForModel, revokeEntityFromModel } from '@/lib/models';
import { Pagination } from '@/types/api';
import { ConfiguredModel } from '@/types/api/model';
import { Role, RoleAssignment } from '@/types/models';
import { Button } from '@components/ui/button';
import HeadingSmall from '@components/ui/heading-small';
import { CheckCircle, Loader, PauseCircle } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router';

interface ModelRolesProps {
    model: ConfiguredModel;
}

export default function ModelRoles({ model }: ModelRolesProps) {
    const api = useApi();
    const isMobile = useIsMobile();
    const { config, user } = useGatekeeper();
    const navigate = useNavigate();

    const [roleAssignments, setRoleAssignments] = useState<Pagination<RoleAssignment> | null>(null);
    const [roleAssignmentsSearchTerm, setRoleAssignmentsSearchTerm] = useState<string>('');
    const [roleAssignmentsLoading, setRoleAssignmentsLoading] = useState<boolean>(false);
    const [roleRevocationLoading, setRoleRevocationLoading] = useState<boolean>(false);
    const [roleAssignmentsError, setRoleAssignmentsError] = useState<string | null>(null);
    const [roleRevocationError, setRoleRevocationError] = useState<string | null>(null);

    const [unassignedRoles, setUnassignedRoles] = useState<Pagination<Role> | null>(null);
    const [unassignedRolesSearchTerm, setUnassignedRolesSearchTerm] = useState<string>('');
    const [unassignedRolesLoading, setUnassignedRolesLoading] = useState<boolean>(false);
    const [roleAssignmentLoading, setRoleAssignmentLoading] = useState<boolean>(false);
    const [unassignedRolesError, setUnassignedRolesError] = useState<string | null>(null);
    const [roleAssignmentError, setRoleAssignmentError] = useState<string | null>(null);

    const numberOfColumns = useMemo(() => {
        return user.permissions.can_manage ? 3 : 2;
    }, [user]) as number;

    const entitySupported: boolean = useMemo(() => config.roles_enabled && model.has_roles && !model.is_role && !model.is_permission, [model]);

    useEffect(() => {
        fetchEntityAssignmentsForModel(
            api,
            model.model_label,
            model.model_pk,
            'role',
            roleAssignmentsSearchTerm,
            roleAssignments?.current_page || 1,
            setRoleAssignments,
            setRoleAssignmentsLoading,
            setRoleAssignmentsError,
        );

        fetchUnassignedEntitiesForModel(
            api,
            model.model_label,
            model.model_pk,
            'role',
            unassignedRolesSearchTerm,
            unassignedRoles?.current_page || 1,
            setUnassignedRoles,
            setUnassignedRolesLoading,
            setUnassignedRolesError,
        );
    }, [model]);

    return (
        <div className="flex w-full flex-col gap-8">
            <div className="flex flex-col gap-4">
                <HeadingSmall title="Assigned Roles" />

                <DebouncedInput
                    value={roleAssignmentsSearchTerm}
                    placeholder="Search by role name"
                    debounceTime={1000}
                    setValue={setRoleAssignmentsSearchTerm}
                    onDebouncedChange={(value) =>
                        fetchEntityAssignmentsForModel(
                            api,
                            model.model_label,
                            model.model_pk,
                            'role',
                            value,
                            1,
                            setRoleAssignments,
                            setRoleAssignmentsLoading,
                            setRoleAssignmentsError,
                        )
                    }
                />

                {roleRevocationError && <div className="w-full px-4 py-6 text-center text-red-500">{roleRevocationError}</div>}

                <div className="overflow-auto rounded-lg border dark:border-gray-700">
                    <table className="w-full text-sm">
                        <thead className="bg-muted">
                            <tr>
                                <th className="px-4 py-2 text-left font-semibold">Role Name</th>
                                <th className="px-4 py-2 text-center font-semibold">Role Status</th>
                                <th className="hidden px-4 py-2 text-center font-semibold sm:table-cell">Assigned Date/Time</th>
                                {user.permissions.can_manage && <th className="px-4 py-2 text-center font-semibold">Action</th>}
                            </tr>
                        </thead>
                        <tbody>
                            {roleAssignmentsLoading ? (
                                <tr>
                                    <td
                                        colSpan={isMobile ? numberOfColumns : numberOfColumns + 1}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        <div className="inline-flex items-center gap-2">
                                            <Loader className="mx-auto h-4 w-4 animate-spin text-gray-500 dark:text-gray-400" />
                                        </div>
                                    </td>
                                </tr>
                            ) : roleAssignmentsError ? (
                                <tr>
                                    <td colSpan={isMobile ? numberOfColumns : numberOfColumns + 1} className="px-4 py-6 text-center text-red-500">
                                        {roleAssignmentsError}
                                    </td>
                                </tr>
                            ) : !roleAssignments?.data.length ? (
                                <tr>
                                    <td
                                        colSpan={isMobile ? numberOfColumns : numberOfColumns + 1}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        No roles assigned.
                                    </td>
                                </tr>
                            ) : (
                                roleAssignments.data.map((assignment) => (
                                    <tr key={assignment.role.name} className="border-t transition-colors dark:border-gray-700">
                                        <td className="px-4 py-2">
                                            <Button
                                                variant={'link'}
                                                onClick={() => {
                                                    navigate(`/roles/${assignment.role.id}/manage`);
                                                }}
                                            >
                                                {assignment.role.name}
                                            </Button>
                                        </td>
                                        <td className="px-4 py-2">
                                            <div className="flex h-8 w-full items-center justify-center">
                                                {assignment.role.is_active ? (
                                                    <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                                                ) : (
                                                    <PauseCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                                                )}
                                            </div>
                                        </td>
                                        <td className="hidden px-4 py-2 text-center sm:table-cell">{assignment.assigned_at || 'N/A'}</td>
                                        {user.permissions.can_manage && (
                                            <td className="px-4 py-2">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    disabled={roleRevocationLoading}
                                                    onClick={async () => {
                                                        await revokeEntityFromModel(
                                                            api,
                                                            {
                                                                model_label: model.model_label,
                                                                model_pk: model.model_pk,
                                                                entity: 'role',
                                                                entity_name: assignment.role.name,
                                                            },
                                                            setRoleRevocationLoading,
                                                            setRoleRevocationError,
                                                        );

                                                        Promise.all([
                                                            fetchEntityAssignmentsForModel(
                                                                api,
                                                                model.model_label,
                                                                model.model_pk,
                                                                'role',
                                                                roleAssignmentsSearchTerm,
                                                                roleAssignments.current_page,
                                                                setRoleAssignments,
                                                                setRoleAssignmentsLoading,
                                                                setRoleAssignmentsError,
                                                            ),
                                                            fetchUnassignedEntitiesForModel(
                                                                api,
                                                                model.model_label,
                                                                model.model_pk,
                                                                'role',
                                                                unassignedRolesSearchTerm,
                                                                unassignedRoles?.current_page || 1,
                                                                setUnassignedRoles,
                                                                setUnassignedRolesLoading,
                                                                setUnassignedRolesError,
                                                            ),
                                                        ]);
                                                    }}
                                                >
                                                    Revoke
                                                </Button>
                                            </td>
                                        )}
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {roleAssignments && roleAssignments.total > 0 && (
                    <div className="flex w-full items-center justify-end gap-2 pt-2">
                        <Button
                            size="sm"
                            variant="outline"
                            disabled={roleAssignments.current_page === 1}
                            onClick={() => {
                                fetchEntityAssignmentsForModel(
                                    api,
                                    model.model_label,
                                    model.model_pk,
                                    'role',
                                    roleAssignmentsSearchTerm,
                                    roleAssignments.current_page - 1,
                                    setRoleAssignments,
                                    setRoleAssignmentsLoading,
                                    setRoleAssignmentsError,
                                );
                            }}
                        >
                            Previous
                        </Button>

                        <span className="text-sm">{`${roleAssignments.from} to ${roleAssignments.to} of ${roleAssignments.total}`}</span>

                        <Button
                            size="sm"
                            variant="outline"
                            disabled={roleAssignments.current_page === roleAssignments.last_page}
                            onClick={() => {
                                fetchEntityAssignmentsForModel(
                                    api,
                                    model.model_label,
                                    model.model_pk,
                                    'role',
                                    roleAssignmentsSearchTerm,
                                    roleAssignments.current_page + 1,
                                    setRoleAssignments,
                                    setRoleAssignmentsLoading,
                                    setRoleAssignmentsError,
                                );
                            }}
                        >
                            Next
                        </Button>
                    </div>
                )}
            </div>

            <div className="flex flex-col gap-4">
                <HeadingSmall title="Unassigned Roles" />

                <DebouncedInput
                    value={unassignedRolesSearchTerm}
                    placeholder="Search by role name"
                    debounceTime={1000}
                    setValue={setUnassignedRolesSearchTerm}
                    onDebouncedChange={(value) =>
                        fetchUnassignedEntitiesForModel(
                            api,
                            model.model_label,
                            model.model_pk,
                            'role',
                            value,
                            1,
                            setUnassignedRoles,
                            setUnassignedRolesLoading,
                            setUnassignedRolesError,
                        )
                    }
                />

                {roleAssignmentError && <div className="w-full px-4 py-6 text-center text-red-500">{roleAssignmentError}</div>}

                <div className="overflow-auto rounded-lg border dark:border-gray-700">
                    <table className="w-full text-sm">
                        <thead className="bg-muted">
                            <tr>
                                <th className="px-4 py-2 text-left font-semibold">Role Name</th>
                                <th className="px-4 py-2 text-center font-semibold">Role Status</th>
                                {user.permissions.can_manage && entitySupported && <th className="px-7 py-2 text-left font-semibold">Action</th>}
                            </tr>
                        </thead>
                        <tbody>
                            {unassignedRolesLoading ? (
                                <tr>
                                    <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
                                        <div className="inline-flex items-center gap-2">
                                            <Loader className="mx-auto h-4 w-4 animate-spin text-gray-500 dark:text-gray-400" />
                                        </div>
                                    </td>
                                </tr>
                            ) : unassignedRolesError ? (
                                <tr>
                                    <td colSpan={numberOfColumns} className="px-4 py-6 text-center text-red-500">
                                        {unassignedRolesError}
                                    </td>
                                </tr>
                            ) : !unassignedRoles?.data.length ? (
                                <tr>
                                    <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
                                        No unassigned roles.
                                    </td>
                                </tr>
                            ) : (
                                unassignedRoles.data.map((role) => (
                                    <tr key={role.name} className="border-t transition-colors dark:border-gray-700">
                                        <td className="px-4 py-2">
                                            <Button
                                                variant={'link'}
                                                onClick={() => {
                                                    navigate(`/roles/${role.id}/manage`);
                                                }}
                                            >
                                                {role.name}
                                            </Button>
                                        </td>
                                        <td className="flex items-center justify-center px-4 py-2">
                                            <div className="flex h-8 w-full items-center justify-center">
                                                {role.is_active ? (
                                                    <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                                                ) : (
                                                    <PauseCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                                                )}
                                            </div>
                                        </td>
                                        {user.permissions.can_manage && entitySupported && (
                                            <td className="px-4 py-2">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    disabled={roleAssignmentLoading}
                                                    onClick={async () => {
                                                        await assignEntityToModel(
                                                            api,
                                                            {
                                                                model_label: model.model_label,
                                                                model_pk: model.model_pk,
                                                                entity: 'role',
                                                                entity_name: role.name,
                                                            },
                                                            setRoleAssignmentLoading,
                                                            setRoleAssignmentError,
                                                        );

                                                        Promise.all([
                                                            fetchEntityAssignmentsForModel(
                                                                api,
                                                                model.model_label,
                                                                model.model_pk,
                                                                'role',
                                                                roleAssignmentsSearchTerm,
                                                                roleAssignments?.current_page || 1,
                                                                setRoleAssignments,
                                                                setRoleAssignmentsLoading,
                                                                setRoleAssignmentsError,
                                                            ),
                                                            fetchUnassignedEntitiesForModel(
                                                                api,
                                                                model.model_label,
                                                                model.model_pk,
                                                                'role',
                                                                unassignedRolesSearchTerm,
                                                                unassignedRoles.current_page,
                                                                setUnassignedRoles,
                                                                setUnassignedRolesLoading,
                                                                setUnassignedRolesError,
                                                            ),
                                                        ]);
                                                    }}
                                                >
                                                    Assign
                                                </Button>
                                            </td>
                                        )}
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {unassignedRoles && unassignedRoles.total > 0 && (
                    <div className="flex w-full items-center justify-end gap-2 pt-2">
                        <Button
                            size="sm"
                            variant="outline"
                            disabled={unassignedRoles.current_page === 1}
                            onClick={() => {
                                fetchUnassignedEntitiesForModel(
                                    api,
                                    model.model_label,
                                    model.model_pk,
                                    'role',
                                    unassignedRolesSearchTerm,
                                    unassignedRoles.current_page - 1,
                                    setUnassignedRoles,
                                    setUnassignedRolesLoading,
                                    setUnassignedRolesError,
                                );
                            }}
                        >
                            Previous
                        </Button>

                        <span className="text-sm">{`${unassignedRoles.from} to ${unassignedRoles.to} of ${unassignedRoles.total}`}</span>

                        <Button
                            size="sm"
                            variant="outline"
                            disabled={unassignedRoles.current_page === unassignedRoles.last_page}
                            onClick={() => {
                                fetchUnassignedEntitiesForModel(
                                    api,
                                    model.model_label,
                                    model.model_pk,
                                    'role',
                                    unassignedRolesSearchTerm,
                                    unassignedRoles.current_page + 1,
                                    setUnassignedRoles,
                                    setUnassignedRolesLoading,
                                    setUnassignedRolesError,
                                );
                            }}
                        >
                            Next
                        </Button>
                    </div>
                )}
            </div>
        </div>
    );
}
