import DeactivateEntity from '@/components/entity/DeactivateEntity';
import DeleteEntity from '@/components/entity/DeleteEntity';
import EntityForm from '@/components/entity/EntityForm';
import EntitySummary from '@/components/entity/EntitySummary';
import GrantEntityByDefault from '@/components/entity/GrantEntityByDefault';
import ReactivateEntity from '@/components/entity/ReactivateEntity';
import RevokeEntityDefaultGrant from '@/components/entity/RevokeEntityDefaultGrant';
import EntityLayout from '@/layouts/entity-layout';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import { useApi } from '@/lib/api';
import { getEntity } from '@/lib/entities';
import { manageEntityText, type ManageEntityText } from '@/lib/lang/en/entity/manage';
import { type GatekeeperEntity, type GatekeeperEntityModelMap } from '@/types';
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
    }, [api, entity, id]);

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
                <EntitySummary<E> entity={entity} entityModel={entityModel} />

                <div className="space-y-6">
                    <EntityForm<E> formType="update" entity={entity} entityModel={entityModel} updateEntity={setEntityModel} />
                </div>

                {entityModel.grant_by_default ? (
                    <RevokeEntityDefaultGrant<E> entity={entity} entityModel={entityModel} updateEntity={setEntityModel} />
                ) : (
                    <GrantEntityByDefault<E> entity={entity} entityModel={entityModel} updateEntity={setEntityModel} />
                )}

                {entityModel.is_active ? (
                    <DeactivateEntity<E> entity={entity} entityModel={entityModel} updateEntity={setEntityModel} />
                ) : (
                    <ReactivateEntity<E> entity={entity} entityModel={entityModel} updateEntity={setEntityModel} />
                )}

                <DeleteEntity<E> entity={entity} entityModel={entityModel} />
            </EntityLayout>
        </GatekeeperLayout>
    );
}
