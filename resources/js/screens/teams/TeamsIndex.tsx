import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import TeamsLayout from '@/layouts/teams-layout';
import TeamsTable from '@components/teams/TeamsTable';
import HeadingSmall from '@components/ui/heading-small';

export default function TeamsIndex() {
    return (
        <GatekeeperLayout>
            <TeamsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Teams Index" description="Take stock of your application's teams" />
                    <TeamsTable />
                </div>
            </TeamsLayout>
        </GatekeeperLayout>
    );
}
