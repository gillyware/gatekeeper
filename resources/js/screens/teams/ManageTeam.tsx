import DeleteTeam from '@/components/teams/DeleteTeam';
import { useGatekeeper } from '@/context/GatekeeperContext';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import TeamsLayout from '@/layouts/teams-layout';
import { useApi } from '@/lib/api';
import { Team } from '@/types/models';
import DeactivateTeam from '@components/teams/DeactivateTeam';
import ReactivateTeam from '@components/teams/ReactivateTeam';
import TeamForm from '@components/teams/TeamForm';
import TeamSummary from '@components/teams/TeamSummary';
import { Loader } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useParams } from 'react-router';

export default function ManageTeam() {
    const api = useApi();
    const { user } = useGatekeeper();
    const { teamId } = useParams<{ teamId: string }>();

    const [team, setTeam] = useState<Team | null>(null);

    const updateTeam = (newTeam: Team) => {
        setTeam(newTeam);
    };

    useEffect(() => {
        const fetchTeam = async () => {
            const response = await api.getTeam({ id: Number(teamId) });

            if (response.status < 400) {
                setTeam(response.data as Team);
            } else {
                console.error('Failed to fetch team:', response.errors);
            }
        };

        fetchTeam();
    }, []);

    return (
        <GatekeeperLayout>
            <TeamsLayout>
                {user.permissions.can_manage && (
                    <>
                        {team ? (
                            <>
                                <TeamSummary team={team} />

                                <div className="space-y-6">
                                    <TeamForm action="update" team={team} updateTeam={updateTeam} />
                                </div>

                                {team.is_active ? (
                                    <DeactivateTeam team={team} updateTeam={updateTeam} />
                                ) : (
                                    <ReactivateTeam team={team} updateTeam={updateTeam} />
                                )}

                                <DeleteTeam team={team} />
                            </>
                        ) : (
                            <Loader className="h-6 w-6 animate-spin text-gray-500 dark:text-gray-400" />
                        )}
                    </>
                )}
            </TeamsLayout>
        </GatekeeperLayout>
    );
}
