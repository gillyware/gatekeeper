import { useGatekeeper } from '@/context/GatekeeperContext';
import { ConfiguredModel } from '@/types/api/model';
import { Card, CardContent } from '@components/ui/card';
import { Ban, CheckCircle } from 'lucide-react';
import { Separator } from '../ui/separator';
import { Tooltip, TooltipContent, TooltipTrigger } from '../ui/tooltip';

interface ModelSummaryProps {
    model: ConfiguredModel;
}

interface SupportIndicatorProps {
    supported: boolean;
    tooltip: string;
}

export default function ModelSummary({ model }: ModelSummaryProps) {
    const { config } = useGatekeeper();

    return (
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <Card>
                <CardContent className="flex flex-1 flex-col gap-2">
                    <div className="flex flex-row items-center justify-between gap-4">
                        <span className="font-bold">Model:</span>
                        <span>{model.model_label}</span>
                    </div>

                    <Separator className="bg-sidebar-border my-1" />

                    {model.displayable.map((x) => (
                        <div key={x.column} className="flex flex-row items-center justify-between gap-4">
                            <span className="font-bold">{x.label}:</span>
                            <span className="truncate">{model.display[x.column] ?? 'N/A'}</span>
                        </div>
                    ))}
                </CardContent>
            </Card>

            <Card>
                <CardContent className="flex flex-1 flex-col gap-2">
                    <div className="flex flex-row items-center justify-start">
                        <span className="font-bold">Supports</span>
                    </div>

                    <Separator className="bg-sidebar-border my-1" />

                    <div className="flex flex-row items-center justify-between gap-4">
                        <span className="font-bold">Permissions:</span>
                        <span>
                            <SupportIndicator
                                supported={model.has_permissions && !model.is_permission}
                                tooltip={
                                    model.is_permission
                                        ? 'Permissions cannot be assigned to other permissions'
                                        : 'Add the `HasPermissions` trait to this model to enable permission assignment.'
                                }
                            />
                        </span>
                    </div>

                    <div className="flex flex-row items-center justify-between gap-4">
                        <span className="font-bold">Roles:</span>
                        <span>
                            <SupportIndicator
                                supported={config.roles_enabled && model.has_roles && !model.is_role && !model.is_permission}
                                tooltip={
                                    !config.roles_enabled
                                        ? "The 'roles' feature is disabled in the configuration"
                                        : model.is_role
                                          ? 'Roles cannot be assigned to other roles'
                                          : model.is_permission
                                            ? 'Roles cannot be assigned to permissions'
                                            : 'Add the `HasRoles` trait to this model to enable role assignment.'
                                }
                            />
                        </span>
                    </div>

                    <div className="flex flex-row items-center justify-between gap-4">
                        <span className="font-bold">Teams:</span>
                        <span>
                            <SupportIndicator
                                supported={config.teams_enabled && model.has_teams && !model.is_team && !model.is_role && !model.is_permission}
                                tooltip={
                                    !config.teams_enabled
                                        ? "The 'teams' feature is disabled in the configuration"
                                        : model.is_team
                                          ? 'Teams cannot be assigned to other teams.'
                                          : model.is_role
                                            ? 'Teams cannot be assigned to roles'
                                            : model.is_permission
                                              ? 'Teams cannot be assigned to permissions'
                                              : 'Add the `HasTeams` trait to this model to enable team assignment.'
                                }
                            />
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

function SupportIndicator({ supported, tooltip }: SupportIndicatorProps) {
    return supported ? (
        <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
    ) : (
        <Tooltip>
            <TooltipTrigger asChild>
                <Ban className="h-4 w-4 text-red-600 dark:text-red-400" />
            </TooltipTrigger>
            <TooltipContent>{tooltip}</TooltipContent>
        </Tooltip>
    );
}
