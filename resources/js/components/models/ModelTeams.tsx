import { DebouncedInput } from '@/components/ui/debounced-input';
import { useGatekeeper } from '@/context/GatekeeperContext';
import { useIsMobile } from '@/hooks/use-mobile';
import { useApi } from '@/lib/api';
import { assignEntityToModel, fetchEntityAssignmentsForModel, fetchUnassignedEntitiesForModel, revokeEntityFromModel } from '@/lib/models';
import { Pagination } from '@/types/api';
import { ConfiguredModel } from '@/types/api/model';
import { Team, TeamAssignment } from '@/types/models';
import { Button } from '@components/ui/button';
import HeadingSmall from '@components/ui/heading-small';
import { CheckCircle, Loader, PauseCircle } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router';

interface ModelTeamsProps {
    model: ConfiguredModel;
}

export default function ModelTeams({ model }: ModelTeamsProps) {
    const api = useApi();
    const isMobile = useIsMobile();
    const { config, user } = useGatekeeper();
    const navigate = useNavigate();

    const [teamAssignments, setTeamAssignments] = useState<Pagination<TeamAssignment> | null>(null);
    const [teamAssignmentsSearchTerm, setTeamAssignmentsSearchTerm] = useState<string>('');
    const [teamAssignmentsLoading, setTeamAssignmentsLoading] = useState<boolean>(false);
    const [teamRevocationLoading, setTeamRevocationLoading] = useState<boolean>(false);
    const [teamAssignmentsError, setTeamAssignmentsError] = useState<string | null>(null);
    const [teamRevocationError, setTeamRevocationError] = useState<string | null>(null);

    const [unassignedTeams, setUnassignedTeams] = useState<Pagination<Team> | null>(null);
    const [unassignedTeamsSearchTerm, setUnassignedTeamsSearchTerm] = useState<string>('');
    const [unassignedTeamsLoading, setUnassignedTeamsLoading] = useState<boolean>(false);
    const [teamAssignmentLoading, setTeamAssignmentLoading] = useState<boolean>(false);
    const [unassignedTeamsError, setUnassignedTeamsError] = useState<string | null>(null);
    const [teamAssignmentError, setTeamAssignmentError] = useState<string | null>(null);

    const numberOfColumns = useMemo(() => {
        return user.permissions.can_manage ? 3 : 2;
    }, [user]) as number;

    const entitySupported: boolean = useMemo(
        () => config.teams_enabled && model.has_teams && !model.is_team && !model.is_role && !model.is_permission,
        [model],
    );

    useEffect(() => {
        fetchEntityAssignmentsForModel(
            api,
            model.model_label,
            model.model_pk,
            'team',
            teamAssignmentsSearchTerm,
            teamAssignments?.current_page || 1,
            setTeamAssignments,
            setTeamAssignmentsLoading,
            setTeamAssignmentsError,
        );

        fetchUnassignedEntitiesForModel(
            api,
            model.model_label,
            model.model_pk,
            'team',
            unassignedTeamsSearchTerm,
            unassignedTeams?.current_page || 1,
            setUnassignedTeams,
            setUnassignedTeamsLoading,
            setUnassignedTeamsError,
        );
    }, [model]);

    return (
        <div className="flex w-full flex-col gap-8">
            <div className="flex flex-col gap-4">
                <HeadingSmall title="Assigned Teams" />

                <DebouncedInput
                    value={teamAssignmentsSearchTerm}
                    placeholder="Search by team name"
                    debounceTime={1000}
                    setValue={setTeamAssignmentsSearchTerm}
                    onDebouncedChange={(value) =>
                        fetchEntityAssignmentsForModel(
                            api,
                            model.model_label,
                            model.model_pk,
                            'team',
                            value,
                            1,
                            setTeamAssignments,
                            setTeamAssignmentsLoading,
                            setTeamAssignmentsError,
                        )
                    }
                />

                {teamRevocationError && <div className="w-full px-4 py-6 text-center text-red-500">{teamRevocationError}</div>}

                <div className="overflow-auto rounded-lg border dark:border-gray-700">
                    <table className="w-full text-sm">
                        <thead className="bg-muted">
                            <tr>
                                <th className="px-4 py-2 text-left font-semibold">Team Name</th>
                                <th className="px-4 py-2 text-center font-semibold">Team Status</th>
                                <th className="hidden px-4 py-2 text-center font-semibold sm:table-cell">Assigned Date/Time</th>
                                {user.permissions.can_manage && <th className="px-4 py-2 text-center font-semibold">Action</th>}
                            </tr>
                        </thead>
                        <tbody>
                            {teamAssignmentsLoading ? (
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
                            ) : teamAssignmentsError ? (
                                <tr>
                                    <td colSpan={isMobile ? numberOfColumns : numberOfColumns + 1} className="px-4 py-6 text-center text-red-500">
                                        {teamAssignmentsError}
                                    </td>
                                </tr>
                            ) : !teamAssignments?.data.length ? (
                                <tr>
                                    <td
                                        colSpan={isMobile ? numberOfColumns : numberOfColumns + 1}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        No teams assigned.
                                    </td>
                                </tr>
                            ) : (
                                teamAssignments.data.map((assignment) => (
                                    <tr key={assignment.team.name} className="border-t transition-colors dark:border-gray-700">
                                        <td className="px-4 py-2">
                                            <Button
                                                variant={'link'}
                                                onClick={() => {
                                                    navigate(`/teams/${assignment.team.id}/manage`);
                                                }}
                                            >
                                                {assignment.team.name}
                                            </Button>
                                        </td>
                                        <td className="px-4 py-2">
                                            <div className="flex h-8 w-full items-center justify-center">
                                                {assignment.team.is_active ? (
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
                                                    disabled={teamRevocationLoading}
                                                    onClick={async () => {
                                                        await revokeEntityFromModel(
                                                            api,
                                                            {
                                                                model_label: model.model_label,
                                                                model_pk: model.model_pk,
                                                                entity: 'team',
                                                                entity_name: assignment.team.name,
                                                            },
                                                            setTeamRevocationLoading,
                                                            setTeamRevocationError,
                                                        );

                                                        Promise.all([
                                                            fetchEntityAssignmentsForModel(
                                                                api,
                                                                model.model_label,
                                                                model.model_pk,
                                                                'team',
                                                                teamAssignmentsSearchTerm,
                                                                teamAssignments.current_page,
                                                                setTeamAssignments,
                                                                setTeamAssignmentsLoading,
                                                                setTeamAssignmentsError,
                                                            ),
                                                            fetchUnassignedEntitiesForModel(
                                                                api,
                                                                model.model_label,
                                                                model.model_pk,
                                                                'team',
                                                                unassignedTeamsSearchTerm,
                                                                unassignedTeams?.current_page || 1,
                                                                setUnassignedTeams,
                                                                setUnassignedTeamsLoading,
                                                                setUnassignedTeamsError,
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

                {teamAssignments && teamAssignments.total > 0 && (
                    <div className="flex w-full items-center justify-end gap-2 pt-2">
                        <Button
                            size="sm"
                            variant="outline"
                            disabled={teamAssignments.current_page === 1}
                            onClick={() => {
                                fetchEntityAssignmentsForModel(
                                    api,
                                    model.model_label,
                                    model.model_pk,
                                    'team',
                                    teamAssignmentsSearchTerm,
                                    teamAssignments.current_page - 1,
                                    setTeamAssignments,
                                    setTeamAssignmentsLoading,
                                    setTeamAssignmentsError,
                                );
                            }}
                        >
                            Previous
                        </Button>

                        <span className="text-sm">{`${teamAssignments.from} to ${teamAssignments.to} of ${teamAssignments.total}`}</span>

                        <Button
                            size="sm"
                            variant="outline"
                            disabled={teamAssignments.current_page === teamAssignments.last_page}
                            onClick={() => {
                                fetchEntityAssignmentsForModel(
                                    api,
                                    model.model_label,
                                    model.model_pk,
                                    'team',
                                    teamAssignmentsSearchTerm,
                                    teamAssignments.current_page + 1,
                                    setTeamAssignments,
                                    setTeamAssignmentsLoading,
                                    setTeamAssignmentsError,
                                );
                            }}
                        >
                            Next
                        </Button>
                    </div>
                )}
            </div>

            <div className="flex flex-col gap-4">
                <HeadingSmall title="Unassigned Teams" />

                <DebouncedInput
                    value={unassignedTeamsSearchTerm}
                    placeholder="Search by team name"
                    debounceTime={1000}
                    setValue={setUnassignedTeamsSearchTerm}
                    onDebouncedChange={(value) =>
                        fetchUnassignedEntitiesForModel(
                            api,
                            model.model_label,
                            model.model_pk,
                            'team',
                            value,
                            1,
                            setUnassignedTeams,
                            setUnassignedTeamsLoading,
                            setUnassignedTeamsError,
                        )
                    }
                />

                {teamAssignmentError && <div className="w-full px-4 py-6 text-center text-red-500">{teamAssignmentError}</div>}

                <div className="overflow-auto rounded-lg border dark:border-gray-700">
                    <table className="w-full text-sm">
                        <thead className="bg-muted">
                            <tr>
                                <th className="px-4 py-2 text-left font-semibold">Team Name</th>
                                <th className="px-4 py-2 text-center font-semibold">Team Status</th>
                                {user.permissions.can_manage && entitySupported && <th className="px-7 py-2 text-left font-semibold">Action</th>}
                            </tr>
                        </thead>
                        <tbody>
                            {unassignedTeamsLoading ? (
                                <tr>
                                    <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
                                        <div className="inline-flex items-center gap-2">
                                            <Loader className="mx-auto h-4 w-4 animate-spin text-gray-500 dark:text-gray-400" />
                                        </div>
                                    </td>
                                </tr>
                            ) : unassignedTeamsError ? (
                                <tr>
                                    <td colSpan={numberOfColumns} className="px-4 py-6 text-center text-red-500">
                                        {unassignedTeamsError}
                                    </td>
                                </tr>
                            ) : !unassignedTeams?.data.length ? (
                                <tr>
                                    <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
                                        No unassigned teams.
                                    </td>
                                </tr>
                            ) : (
                                unassignedTeams.data.map((team) => (
                                    <tr key={team.name} className="border-t transition-colors dark:border-gray-700">
                                        <td className="px-4 py-2">
                                            <Button
                                                variant={'link'}
                                                onClick={() => {
                                                    navigate(`/teams/${team.id}/manage`);
                                                }}
                                            >
                                                {team.name}
                                            </Button>
                                        </td>
                                        <td className="flex items-center justify-center px-4 py-2">
                                            <div className="flex h-8 w-full items-center justify-center">
                                                {team.is_active ? (
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
                                                    disabled={teamAssignmentLoading}
                                                    onClick={async () => {
                                                        await assignEntityToModel(
                                                            api,
                                                            {
                                                                model_label: model.model_label,
                                                                model_pk: model.model_pk,
                                                                entity: 'team',
                                                                entity_name: team.name,
                                                            },
                                                            setTeamAssignmentLoading,
                                                            setTeamAssignmentError,
                                                        );

                                                        Promise.all([
                                                            fetchEntityAssignmentsForModel(
                                                                api,
                                                                model.model_label,
                                                                model.model_pk,
                                                                'team',
                                                                teamAssignmentsSearchTerm,
                                                                teamAssignments?.current_page || 1,
                                                                setTeamAssignments,
                                                                setTeamAssignmentsLoading,
                                                                setTeamAssignmentsError,
                                                            ),
                                                            fetchUnassignedEntitiesForModel(
                                                                api,
                                                                model.model_label,
                                                                model.model_pk,
                                                                'team',
                                                                unassignedTeamsSearchTerm,
                                                                unassignedTeams.current_page,
                                                                setUnassignedTeams,
                                                                setUnassignedTeamsLoading,
                                                                setUnassignedTeamsError,
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

                {unassignedTeams && unassignedTeams.total > 0 && (
                    <div className="flex w-full items-center justify-end gap-2 pt-2">
                        <Button
                            size="sm"
                            variant="outline"
                            disabled={unassignedTeams.current_page === 1}
                            onClick={() => {
                                fetchUnassignedEntitiesForModel(
                                    api,
                                    model.model_label,
                                    model.model_pk,
                                    'team',
                                    unassignedTeamsSearchTerm,
                                    unassignedTeams.current_page - 1,
                                    setUnassignedTeams,
                                    setUnassignedTeamsLoading,
                                    setUnassignedTeamsError,
                                );
                            }}
                        >
                            Previous
                        </Button>

                        <span className="text-sm">{`${unassignedTeams.from} to ${unassignedTeams.to} of ${unassignedTeams.total}`}</span>

                        <Button
                            size="sm"
                            variant="outline"
                            disabled={unassignedTeams.current_page === unassignedTeams.last_page}
                            onClick={() => {
                                fetchUnassignedEntitiesForModel(
                                    api,
                                    model.model_label,
                                    model.model_pk,
                                    'team',
                                    unassignedTeamsSearchTerm,
                                    unassignedTeams.current_page + 1,
                                    setUnassignedTeams,
                                    setUnassignedTeamsLoading,
                                    setUnassignedTeamsError,
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
