import { DebouncedInput } from '@/components/ui/debounced-input';
import { useGatekeeper } from '@/context/GatekeeperContext';
import { useIsMobile } from '@/hooks/use-mobile';
import { useApi } from '@/lib/api';
import { assignEntityToModel, fetchEntityAssignmentsForModel, fetchUnassignedEntitiesForModel, revokeEntityFromModel } from '@/lib/models';
import { Pagination } from '@/types/api';
import { ConfiguredModel } from '@/types/api/model';
import { Permission, PermissionAssignment } from '@/types/models';
import { Button } from '@components/ui/button';
import HeadingSmall from '@components/ui/heading-small';
import { CheckCircle, Loader, PauseCircle } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

interface ModelPermissionsProps {
    model: ConfiguredModel;
}

export default function ModelPermissions({ model }: ModelPermissionsProps) {
    const api = useApi();
    const isMobile = useIsMobile();
    const { user } = useGatekeeper();

    const [permissionAssignments, setPermissionAssignments] = useState<Pagination<PermissionAssignment> | null>(null);
    const [permissionAssignmentsSearchTerm, setPermissionAssignmentsSearchTerm] = useState<string>('');
    const [permissionAssignmentsLoading, setPermissionAssignmentsLoading] = useState<boolean>(false);
    const [permissionRevocationLoading, setPermissionRevocationLoading] = useState<boolean>(false);
    const [permissionAssignmentsError, setPermissionAssignmentsError] = useState<string | null>(null);
    const [permissionRevocationError, setPermissionRevocationError] = useState<string | null>(null);

    const [unassignedPermissions, setUnassignedPermissions] = useState<Pagination<Permission> | null>(null);
    const [unassignedPermissionsSearchTerm, setUnassignedPermissionsSearchTerm] = useState<string>('');
    const [unassignedPermissionsLoading, setUnassignedPermissionsLoading] = useState<boolean>(false);
    const [permissionAssignmentLoading, setPermissionAssignmentLoading] = useState<boolean>(false);
    const [unassignedPermissionsError, setUnassignedPermissionsError] = useState<string | null>(null);
    const [permissionAssignmentError, setPermissionAssignmentError] = useState<string | null>(null);

    const numberOfColumns = useMemo(() => {
        return user.permissions.can_manage ? 3 : 2;
    }, [user]) as number;

    useEffect(() => {
        fetchEntityAssignmentsForModel(
            api,
            model.model_label,
            model.model_pk,
            'permission',
            permissionAssignmentsSearchTerm,
            permissionAssignments?.current_page || 1,
            setPermissionAssignments,
            setPermissionAssignmentsLoading,
            setPermissionAssignmentsError,
        );

        fetchUnassignedEntitiesForModel(
            api,
            model.model_label,
            model.model_pk,
            'permission',
            unassignedPermissionsSearchTerm,
            unassignedPermissions?.current_page || 1,
            setUnassignedPermissions,
            setUnassignedPermissionsLoading,
            setUnassignedPermissionsError,
        );
    }, [model]);

    return (
        <div className="flex w-full flex-col gap-8">
            <div className="flex flex-col gap-4">
                <HeadingSmall title="Assigned Permissions" />

                <DebouncedInput
                    value={permissionAssignmentsSearchTerm}
                    placeholder="Search by permission name"
                    debounceTime={1000}
                    setValue={setPermissionAssignmentsSearchTerm}
                    onDebouncedChange={(value) =>
                        fetchEntityAssignmentsForModel(
                            api,
                            model.model_label,
                            model.model_pk,
                            'permission',
                            value,
                            1,
                            setPermissionAssignments,
                            setPermissionAssignmentsLoading,
                            setPermissionAssignmentsError,
                        )
                    }
                />

                {permissionRevocationError && <div className="w-full px-4 py-6 text-center text-red-500">{permissionRevocationError}</div>}

                <div className="overflow-auto rounded-lg border dark:border-gray-700">
                    <table className="w-full text-sm">
                        <thead className="bg-muted">
                            <tr>
                                <th className="px-4 py-2 text-left font-semibold">Permission Name</th>
                                <th className="px-4 py-2 text-center font-semibold">Permission Status</th>
                                <th className="hidden px-4 py-2 text-center font-semibold sm:table-cell">Assigned Date/Time</th>
                                {user.permissions.can_manage && <th className="px-4 py-2 text-center font-semibold">Action</th>}
                            </tr>
                        </thead>
                        <tbody>
                            {permissionAssignmentsLoading ? (
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
                            ) : permissionAssignmentsError ? (
                                <tr>
                                    <td colSpan={isMobile ? numberOfColumns : numberOfColumns + 1} className="px-4 py-6 text-center text-red-500">
                                        {permissionAssignmentsError}
                                    </td>
                                </tr>
                            ) : !permissionAssignments?.data.length ? (
                                <tr>
                                    <td
                                        colSpan={isMobile ? numberOfColumns : numberOfColumns + 1}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        No permissions assigned.
                                    </td>
                                </tr>
                            ) : (
                                permissionAssignments.data.map((assignment) => (
                                    <tr key={assignment.permission.name} className="border-t transition-colors dark:border-gray-700">
                                        <td className="px-4 py-2">{assignment.permission.name || 'N/A'}</td>
                                        <td className="px-4 py-2">
                                            <div className="flex h-8 w-full items-center justify-center">
                                                {assignment.permission.is_active ? (
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
                                                    disabled={permissionRevocationLoading}
                                                    onClick={async () => {
                                                        await revokeEntityFromModel(
                                                            api,
                                                            {
                                                                model_label: model.model_label,
                                                                model_pk: model.model_pk,
                                                                entity: 'permission',
                                                                entity_name: assignment.permission.name,
                                                            },
                                                            setPermissionRevocationLoading,
                                                            setPermissionRevocationError,
                                                        );

                                                        Promise.all([
                                                            fetchEntityAssignmentsForModel(
                                                                api,
                                                                model.model_label,
                                                                model.model_pk,
                                                                'permission',
                                                                permissionAssignmentsSearchTerm,
                                                                permissionAssignments.current_page,
                                                                setPermissionAssignments,
                                                                setPermissionAssignmentsLoading,
                                                                setPermissionAssignmentsError,
                                                            ),
                                                            fetchUnassignedEntitiesForModel(
                                                                api,
                                                                model.model_label,
                                                                model.model_pk,
                                                                'permission',
                                                                unassignedPermissionsSearchTerm,
                                                                unassignedPermissions?.current_page || 1,
                                                                setUnassignedPermissions,
                                                                setUnassignedPermissionsLoading,
                                                                setUnassignedPermissionsError,
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

                {permissionAssignments && permissionAssignments.total > 0 && (
                    <div className="flex w-full items-center justify-end gap-2 pt-2">
                        <Button
                            size="sm"
                            variant="outline"
                            disabled={permissionAssignments.current_page === 1}
                            onClick={() => {
                                fetchEntityAssignmentsForModel(
                                    api,
                                    model.model_label,
                                    model.model_pk,
                                    'permission',
                                    permissionAssignmentsSearchTerm,
                                    permissionAssignments.current_page - 1,
                                    setPermissionAssignments,
                                    setPermissionAssignmentsLoading,
                                    setPermissionAssignmentsError,
                                );
                            }}
                        >
                            Previous
                        </Button>

                        <span className="text-sm">{`${permissionAssignments.from} to ${permissionAssignments.to} of ${permissionAssignments.total}`}</span>

                        <Button
                            size="sm"
                            variant="outline"
                            disabled={permissionAssignments.current_page === permissionAssignments.last_page}
                            onClick={() => {
                                fetchEntityAssignmentsForModel(
                                    api,
                                    model.model_label,
                                    model.model_pk,
                                    'permission',
                                    permissionAssignmentsSearchTerm,
                                    permissionAssignments.current_page + 1,
                                    setPermissionAssignments,
                                    setPermissionAssignmentsLoading,
                                    setPermissionAssignmentsError,
                                );
                            }}
                        >
                            Next
                        </Button>
                    </div>
                )}
            </div>

            <div className="flex flex-col gap-4">
                <HeadingSmall title="Unassigned Permissions" />

                <DebouncedInput
                    value={unassignedPermissionsSearchTerm}
                    placeholder="Search by permission name"
                    debounceTime={1000}
                    setValue={setUnassignedPermissionsSearchTerm}
                    onDebouncedChange={(value) =>
                        fetchUnassignedEntitiesForModel(
                            api,
                            model.model_label,
                            model.model_pk,
                            'permission',
                            value,
                            1,
                            setUnassignedPermissions,
                            setUnassignedPermissionsLoading,
                            setUnassignedPermissionsError,
                        )
                    }
                />

                {permissionAssignmentError && <div className="w-full px-4 py-6 text-center text-red-500">{permissionAssignmentError}</div>}

                <div className="overflow-auto rounded-lg border dark:border-gray-700">
                    <table className="w-full text-sm">
                        <thead className="bg-muted">
                            <tr>
                                <th className="px-4 py-2 text-left font-semibold">Permission Name</th>
                                <th className="px-4 py-2 text-center font-semibold">Permission Status</th>
                                {user.permissions.can_manage && <th className="px-7 py-2 text-left font-semibold">Action</th>}
                            </tr>
                        </thead>
                        <tbody>
                            {unassignedPermissionsLoading ? (
                                <tr>
                                    <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
                                        <div className="inline-flex items-center gap-2">
                                            <Loader className="mx-auto h-4 w-4 animate-spin text-gray-500 dark:text-gray-400" />
                                        </div>
                                    </td>
                                </tr>
                            ) : unassignedPermissionsError ? (
                                <tr>
                                    <td colSpan={numberOfColumns} className="px-4 py-6 text-center text-red-500">
                                        {unassignedPermissionsError}
                                    </td>
                                </tr>
                            ) : !unassignedPermissions?.data.length ? (
                                <tr>
                                    <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
                                        No unassigned permissions.
                                    </td>
                                </tr>
                            ) : (
                                unassignedPermissions.data.map((permission) => (
                                    <tr key={permission.name} className="border-t transition-colors dark:border-gray-700">
                                        <td className="px-4 py-2">{permission.name || 'N/A'}</td>
                                        <td className="flex items-center justify-center px-4 py-2">
                                            <div className="flex h-8 w-full items-center justify-center">
                                                {permission.is_active ? (
                                                    <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                                                ) : (
                                                    <PauseCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                                                )}
                                            </div>
                                        </td>
                                        {user.permissions.can_manage && (
                                            <td className="px-4 py-2">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    disabled={permissionAssignmentLoading}
                                                    onClick={async () => {
                                                        await assignEntityToModel(
                                                            api,
                                                            {
                                                                model_label: model.model_label,
                                                                model_pk: model.model_pk,
                                                                entity: 'permission',
                                                                entity_name: permission.name,
                                                            },
                                                            setPermissionAssignmentLoading,
                                                            setPermissionAssignmentError,
                                                        );

                                                        Promise.all([
                                                            fetchEntityAssignmentsForModel(
                                                                api,
                                                                model.model_label,
                                                                model.model_pk,
                                                                'permission',
                                                                permissionAssignmentsSearchTerm,
                                                                permissionAssignments?.current_page || 1,
                                                                setPermissionAssignments,
                                                                setPermissionAssignmentsLoading,
                                                                setPermissionAssignmentsError,
                                                            ),
                                                            fetchUnassignedEntitiesForModel(
                                                                api,
                                                                model.model_label,
                                                                model.model_pk,
                                                                'permission',
                                                                unassignedPermissionsSearchTerm,
                                                                unassignedPermissions.current_page,
                                                                setUnassignedPermissions,
                                                                setUnassignedPermissionsLoading,
                                                                setUnassignedPermissionsError,
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

                {unassignedPermissions && unassignedPermissions.total > 0 && (
                    <div className="flex w-full items-center justify-end gap-2 pt-2">
                        <Button
                            size="sm"
                            variant="outline"
                            disabled={unassignedPermissions.current_page === 1}
                            onClick={() => {
                                fetchUnassignedEntitiesForModel(
                                    api,
                                    model.model_label,
                                    model.model_pk,
                                    'permission',
                                    unassignedPermissionsSearchTerm,
                                    unassignedPermissions.current_page - 1,
                                    setUnassignedPermissions,
                                    setUnassignedPermissionsLoading,
                                    setUnassignedPermissionsError,
                                );
                            }}
                        >
                            Previous
                        </Button>

                        <span className="text-sm">{`${unassignedPermissions.from} to ${unassignedPermissions.to} of ${unassignedPermissions.total}`}</span>

                        <Button
                            size="sm"
                            variant="outline"
                            disabled={unassignedPermissions.current_page === unassignedPermissions.last_page}
                            onClick={() => {
                                fetchUnassignedEntitiesForModel(
                                    api,
                                    model.model_label,
                                    model.model_pk,
                                    'permission',
                                    unassignedPermissionsSearchTerm,
                                    unassignedPermissions.current_page + 1,
                                    setUnassignedPermissions,
                                    setUnassignedPermissionsLoading,
                                    setUnassignedPermissionsError,
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
