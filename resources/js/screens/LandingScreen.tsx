import { useGatekeeper } from '@/context/GatekeeperContext';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card';
import { FileClock, KeyRound, LucideIcon, Shapes, ShieldCheck, Users } from 'lucide-react';
import { useMemo } from 'react';
import { Link } from 'react-router-dom';

type Tile = {
    to: string;
    title: string;
    icon: LucideIcon;
    description: string;
};

export default function LandingScreen() {
    const { config } = useGatekeeper();

    const tiles: Tile[] = useMemo(
        () =>
            [
                {
                    to: '/permissions',
                    title: 'Permissions',
                    icon: KeyRound,
                    description: "Manage your application's permissions",
                },
                config.roles_enabled && {
                    to: '/roles',
                    title: 'Roles',
                    icon: ShieldCheck,
                    description: "Manage your application's roles",
                },
                config.teams_enabled && {
                    to: '/teams',
                    title: 'Teams',
                    icon: Users,
                    description: "Manage your application's teams",
                },
                {
                    to: '/models',
                    title: 'Models',
                    icon: Shapes,
                    description: 'Manage access for models in your application',
                },
                config.audit_enabled && {
                    to: '/audit',
                    title: 'Audit',
                    icon: FileClock,
                    description: 'Oversee Gatekeeper changes in your application',
                },
            ].filter((x) => Boolean(x)) as Tile[],
        [config],
    );

    return (
        <GatekeeperLayout>
            <div className="space-y-8 p-6">
                <div>
                    <h1 className="text-2xl font-bold">Gatekeeper</h1>
                    <p className="text-muted-foreground">Manage permissions, roles, teams, and model access for your application</p>
                </div>

                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {tiles.map((tile: Tile) => (
                        <Link key={tile.to} to={tile.to} className="h-full">
                            <Card className="flex h-full min-h-[140px] flex-col transition-shadow hover:shadow-md">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-lg font-medium">{tile.title}</CardTitle>
                                    {<tile.icon className="text-primary h-5 w-5" />}
                                </CardHeader>
                                <CardContent className="flex-1">
                                    <p className="text-muted-foreground text-sm">{tile.description}</p>
                                </CardContent>
                            </Card>
                        </Link>
                    ))}
                </div>
            </div>
        </GatekeeperLayout>
    );
}
