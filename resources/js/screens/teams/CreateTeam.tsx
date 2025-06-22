import { useGatekeeper } from '@/context/GatekeeperContext';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import TeamsLayout from '@/layouts/teams-layout';
import TeamForm from '@components/teams/TeamForm';
import HeadingSmall from '@components/ui/heading-small';

export default function CreateTeam() {
    const { user } = useGatekeeper();

    return (
        <GatekeeperLayout>
            <TeamsLayout>
                {user.permissions.can_manage && (
                    <div className="space-y-6">
                        <HeadingSmall title="Create Team" description="Introduce a new team into your application" />
                        <TeamForm action={'create'} />
                    </div>
                )}
            </TeamsLayout>
        </GatekeeperLayout>
    );
}
