import { useGatekeeper } from '@/context/GatekeeperContext';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import { landingText, type LandingText } from '@/lib/lang/en/landing';
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
    const landing: LandingText = useMemo(() => landingText, []);

    const tiles: Tile[] = useMemo(
        () =>
            [
                {
                    to: '/permissions',
                    title: landing.tiles.permissions.title,
                    icon: KeyRound,
                    description: landing.tiles.permissions.description,
                },
                config.roles_enabled && {
                    to: '/roles',
                    title: landing.tiles.roles.title,
                    icon: ShieldCheck,
                    description: landing.tiles.roles.description,
                },
                config.teams_enabled && {
                    to: '/teams',
                    title: landing.tiles.teams.title,
                    icon: Users,
                    description: landing.tiles.teams.description,
                },
                {
                    to: '/models',
                    title: landing.tiles.models.title,
                    icon: Shapes,
                    description: landing.tiles.models.description,
                },
                config.audit_enabled && {
                    to: '/audit',
                    title: landing.tiles.audit.title,
                    icon: FileClock,
                    description: landing.tiles.audit.description,
                },
            ].filter((x) => Boolean(x)) as Tile[],
        [config],
    );

    return (
        <GatekeeperLayout>
            <div className="space-y-8 p-6">
                <div>
                    <h1 className="text-2xl font-bold">{landing.title}</h1>
                    <p className="text-muted-foreground">{landing.description}</p>
                </div>

                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 md:max-w-4xl lg:grid-cols-3">
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
