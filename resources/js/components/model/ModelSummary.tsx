import { useGatekeeper } from '@/context/GatekeeperContext';
import { getEntitySupportForModel } from '@/lib/models';
import { type ConfiguredModel, type ModelEntitySupport } from '@/types/api/model';
import { Card, CardContent } from '@components/ui/card';
import { Separator } from '@components/ui/separator';
import { Tooltip, TooltipContent, TooltipTrigger } from '@components/ui/tooltip';
import { Ban, CheckCircle } from 'lucide-react';
import { useMemo } from 'react';

interface ModelSummaryProps {
    model: ConfiguredModel;
}

interface SupportIndicatorProps {
    supported: boolean;
    tooltip: string;
}

export default function ModelSummary({ model }: ModelSummaryProps) {
    const { config } = useGatekeeper();
    const entitySupport: ModelEntitySupport = useMemo(() => getEntitySupportForModel(config, model), [config, model]);

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
                            <SupportIndicator supported={entitySupport.permission.supported} tooltip={entitySupport.permission.reason || ''} />
                        </span>
                    </div>

                    <div className="flex flex-row items-center justify-between gap-4">
                        <span className="font-bold">Roles:</span>
                        <span>
                            <SupportIndicator supported={entitySupport.role.supported} tooltip={entitySupport.role.reason || ''} />
                        </span>
                    </div>

                    <div className="flex flex-row items-center justify-between gap-4">
                        <span className="font-bold">Teams:</span>
                        <span>
                            <SupportIndicator supported={entitySupport.team.supported} tooltip={entitySupport.team.reason || ''} />
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
