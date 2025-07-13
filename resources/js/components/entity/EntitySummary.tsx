import { manageEntityText, type EntitySummaryText } from '@/lib/lang/en/entity/manage';
import { type GatekeeperEntity, type GatekeeperEntityModelMap } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card';
import { CheckCircle, Info, PauseCircle } from 'lucide-react';
import { useMemo } from 'react';

interface EntitySummaryProps<E extends GatekeeperEntity> {
    entity: GatekeeperEntity;
    entityModel: GatekeeperEntityModelMap[E];
}

export default function EntitySummary<E extends GatekeeperEntity>({ entity, entityModel }: EntitySummaryProps<E>) {
    const language: EntitySummaryText = useMemo(() => manageEntityText[entity].entitySummaryText, [entity]);

    return (
        <Card className="flex flex-col gap-4">
            <CardHeader className="flex flex-row items-center justify-between space-y-0">
                <CardTitle className="text-md font-medium">{language.title}</CardTitle>
                <Info />
            </CardHeader>
            <CardContent className="flex-1 gap-2">
                <div className="flex flex-row items-center justify-start gap-2">
                    <span className="min-w-[60px] font-bold">{language.nameLabel}</span>
                    <span>{entityModel.name}</span>
                </div>
                <div className="flex flex-row items-center justify-start gap-2">
                    <span className="min-w-[60px] font-bold">{language.statusLabel}</span>

                    {entityModel.is_active ? (
                        <div className="flex items-center justify-start gap-2">
                            <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                            <span className="text-green-700 dark:text-green-300">{language.active}</span>
                        </div>
                    ) : (
                        <div className="flex items-center justify-start gap-2">
                            <PauseCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                            <span className="text-yellow-700 dark:text-yellow-300">{language.inactive}</span>
                        </div>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
