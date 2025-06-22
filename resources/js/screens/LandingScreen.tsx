import { useGatekeeper } from '@/context/GatekeeperContext';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card';
import { FileClock, KeyRound, Shapes, ShieldCheck, Users } from 'lucide-react';
import { Link } from 'react-router-dom';

export default function LandingScreen() {
    const { config } = useGatekeeper();

    return (
        <GatekeeperLayout>
            <div className="space-y-8 p-6">
                <div>
                    <h1 className="text-2xl font-bold">Gatekeeper Dashboard</h1>
                    <p className="text-muted-foreground">Manage roles, permissions, and access control with ease.</p>
                </div>

                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {[
                        {
                            to: '/permissions',
                            title: 'Permissions',
                            icon: <KeyRound className="text-primary h-5 w-5" />,
                            description: "Manage your application's permissions",
                        },
                        config.roles_enabled
                            ? {
                                  to: '/roles',
                                  title: 'Roles',
                                  icon: <ShieldCheck className="text-primary h-5 w-5" />,
                                  description: "Manage your application's roles",
                              }
                            : {},
                        config.teams_enabled
                            ? {
                                  to: '/teams',
                                  title: 'Teams',
                                  icon: <Users className="text-primary h-5 w-5" />,
                                  description: "Manage your application's teams",
                              }
                            : {},
                        {
                            to: '/models',
                            title: 'Models',
                            icon: <Shapes className="text-primary h-5 w-5" />,
                            description: 'Manage access for models in your application',
                        },
                        config.audit_enabled
                            ? {
                                  to: '/audit',
                                  title: 'Audit',
                                  icon: <FileClock className="text-primary h-5 w-5" />,
                                  description: 'Oversee Gatekeeper changes in your application',
                              }
                            : {},
                    ]
                        .filter((x) => Object.keys(x).length > 0)
                        .map(({ to, title, icon, description }) => (
                            <Link key={to} to={to as string} className="h-full">
                                <Card className="flex h-full min-h-[140px] flex-col transition-shadow hover:shadow-md">
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-lg font-medium">{title}</CardTitle>
                                        {icon}
                                    </CardHeader>
                                    <CardContent className="flex-1">
                                        <p className="text-muted-foreground text-sm">{description}</p>
                                    </CardContent>
                                </Card>
                            </Link>
                        ))}
                </div>
            </div>
        </GatekeeperLayout>
    );
}
