import DeactivateEntity from '@/components/entity/DeactivateEntity';
import DeleteEntity from '@/components/entity/DeleteEntity';
import EntityForm from '@/components/entity/EntityForm';
import EntitySummary from '@/components/entity/EntitySummary';
import ReactivateEntity from '@/components/entity/ReactivateEntity';
import EntityLayout from '@/layouts/entity-layout';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import { useApi } from '@/lib/api';
import { getEntity } from '@/lib/entities';
import { manageEntityText, type ManageEntityText } from '@/lib/lang/en/entity/manage';
import { type GatekeeperEntity, type GatekeeperEntityModelMap, type GatekeeperPermission } from '@/types';
import { Loader } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { useParams } from 'react-router';

interface ManageEntityScreenProps {
    entity: GatekeeperEntity;
}

export default function ManageEntityScreen<E extends GatekeeperEntity>({ entity }: ManageEntityScreenProps) {
    const api = useApi();
    const { id } = useParams<{ id: string }>() as { id: string };

    const [entityModel, setEntityModel] = useState<GatekeeperEntityModelMap[E] | null>(null);
    const [loadingEntity, setLoadingEntity] = useState<boolean>(true);
    const [errorLoadingEntity, setErrorLoadingEntity] = useState<string | null>(null);

    const language: ManageEntityText = useMemo(() => manageEntityText[entity], [entity]);

    useEffect(() => {
        getEntity(api, entity, { id }, setEntityModel, setLoadingEntity, setErrorLoadingEntity);
    }, [entity]);

    if (loadingEntity) {
        return (
            <div className="flex h-full w-full items-center justify-center">
                <Loader className="h-6 w-6 animate-spin text-gray-500 dark:text-gray-400" />
            </div>
        );
    }

    if (errorLoadingEntity || !entityModel) {
        return <div className="text-red-500">{errorLoadingEntity || language.failedToLoad}</div>;
    }

    return (
        <GatekeeperLayout>
            <EntityLayout entity={entity}>
                <EntitySummary<GatekeeperPermission> entity={entity} entityModel={entityModel} />

                <div className="space-y-6">
                    <EntityForm<GatekeeperPermission> formType="update" entity={entity} entityModel={entityModel} updateEntity={setEntityModel} />
                </div>

                {entityModel.is_active ? (
                    <DeactivateEntity<GatekeeperPermission> entity={entity} entityModel={entityModel} updateEntity={setEntityModel} />
                ) : (
                    <ReactivateEntity<GatekeeperPermission> entity={entity} entityModel={entityModel} updateEntity={setEntityModel} />
                )}

                <DeleteEntity<GatekeeperPermission> entity={entity} entityModel={entityModel} />
            </EntityLayout>
        </GatekeeperLayout>
    );
}
