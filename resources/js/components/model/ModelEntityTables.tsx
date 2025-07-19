import { useGatekeeper } from '@/context/GatekeeperContext';
import { useApi } from '@/lib/api';
import {
    assignEntityToModel,
    getEntityAssignmentsForModel,
    getEntitySupportForModel,
    getUnassignedEntitiesForModel,
    revokeEntityFromModel,
} from '@/lib/models';
import { type GatekeeperEntity, type GatekeeperEntityModelMap, type GatekeeperModelEntityAssignmentMap } from '@/types';
import { type Pagination } from '@/types/api';
import {
    type AssignEntityToModelRequest,
    type ConfiguredModel,
    type GetModelEntitiesPageRequest,
    type ModelEntitySupport,
    type RevokeEntityFromModelRequest,
} from '@/types/api/model';
import ModelEntityAssignmentsTable from '@components/model/ModelEntityAssignmentsTable';
import ModelUnassignedEntitiesTable from '@components/model/ModelUnassignedEntitiesTable';
import { useEffect, useMemo, useState } from 'react';

interface ModelEntitiesProps {
    model: ConfiguredModel;
    entity: GatekeeperEntity;
    refreshModel: () => Promise<void>;
}

export default function ModelEntityTables<E extends GatekeeperEntity>({ model, entity, refreshModel }: ModelEntitiesProps) {
    const api = useApi();
    const { config, user } = useGatekeeper();

    const initialPageRequest: GetModelEntitiesPageRequest = {
        entity: entity,
        model_label: model.model_label,
        model_pk: model.model_pk,
        page: 1,
        search_term: '',
    };

    const [modelEntityAssignments, setModelEntityAssignments] = useState<Pagination<GatekeeperModelEntityAssignmentMap[E]> | null>(null);
    const [modelEntityAssignmentsSearchTerm, setModelEntityAssignmentsSearchTerm] = useState<string>('');
    const [modelEntityAssignmentsPageRequest, setModelEntityAssignmentsPageRequest] = useState<GetModelEntitiesPageRequest>(initialPageRequest);
    const [loadingModelEntityAssignments, setLoadingModelEntityAssignments] = useState<boolean>(false);
    const [errorLoadingModelEntityAssignments, setErrorLoadingModelEntityAssignments] = useState<string | null>(null);

    const [modelUnassignedEntities, setModelUnassignedEntities] = useState<Pagination<GatekeeperEntityModelMap[E]> | null>(null);
    const [modelUnassignedEntitiesSearchTerm, setModelUnassignedEntitiesSearchTerm] = useState<string>('');
    const [modelUnassignedEntitiesPageRequest, setModelUnassignedEntitiesPageRequest] = useState<GetModelEntitiesPageRequest>(initialPageRequest);
    const [loadingModelUnassignedEntities, setLoadingModelUnassignedEntities] = useState<boolean>(false);
    const [errorLoadingModelUnassignedEntities, setErrorLoadingModelUnassignedEntities] = useState<string | null>(null);

    const [processingEntityRevocation, setProcessingEntityRevocation] = useState<boolean>(false);
    const [errorRevokingEntity, setErrorRevokingEntity] = useState<string | null>(null);
    const [processingEntityAssignment, setProcessingEntityAssignment] = useState<boolean>(false);
    const [errorAssigningEntity, setErrorAssigningEntity] = useState<string | null>(null);

    const modelEntityAssignmentsNumberOfColumns = useMemo(() => (user.permissions.can_manage ? 4 : 3), [user]);
    const modelUnassignedEntitiesNumberOfColumns = useMemo(() => (user.permissions.can_manage ? 3 : 2), [user]);

    const modelEntitySupport: ModelEntitySupport = useMemo(() => getEntitySupportForModel(config, model), [config, model]);

    useEffect(() => {
        setModelEntityAssignments(null);
        setModelEntityAssignmentsSearchTerm('');
        setModelEntityAssignmentsPageRequest(initialPageRequest);

        getEntityAssignmentsForModel(
            api,
            initialPageRequest,
            setModelEntityAssignments,
            setLoadingModelEntityAssignments,
            setErrorLoadingModelEntityAssignments,
        );

        setModelUnassignedEntities(null);
        setModelUnassignedEntitiesSearchTerm('');
        setModelUnassignedEntitiesPageRequest(initialPageRequest);

        getUnassignedEntitiesForModel(
            api,
            initialPageRequest,
            setModelUnassignedEntities,
            setLoadingModelUnassignedEntities,
            setErrorLoadingModelUnassignedEntities,
        );
    }, [entity]);

    useEffect(() => {
        if (modelEntityAssignments !== null) {
            getEntityAssignmentsForModel(
                api,
                modelEntityAssignmentsPageRequest,
                setModelEntityAssignments,
                setLoadingModelEntityAssignments,
                setErrorLoadingModelEntityAssignments,
            );
        }
    }, [modelEntityAssignmentsPageRequest]);

    useEffect(() => {
        if (modelUnassignedEntities !== null) {
            getUnassignedEntitiesForModel(
                api,
                modelUnassignedEntitiesPageRequest,
                setModelUnassignedEntities,
                setLoadingModelUnassignedEntities,
                setErrorLoadingModelUnassignedEntities,
            );
        }
    }, [modelUnassignedEntitiesPageRequest]);

    const refreshPages = async () => {
        await Promise.all([
            setModelEntityAssignmentsPageRequest((prev) => ({ ...prev })),
            setModelUnassignedEntitiesPageRequest((prev) => ({ ...prev })),
        ]);

        refreshModel();
    };

    return (
        <div className="flex w-full flex-col gap-8">
            <ModelEntityAssignmentsTable<E>
                entity={entity}
                modelEntityAssignments={modelEntityAssignments}
                searchTerm={modelEntityAssignmentsSearchTerm}
                pageRequest={modelEntityAssignmentsPageRequest}
                setSearchTerm={setModelEntityAssignmentsSearchTerm}
                setPageRequest={setModelEntityAssignmentsPageRequest}
                refreshPages={refreshPages}
                revokeEntityFromModel={async (entityName: string) => {
                    const request: RevokeEntityFromModelRequest = {
                        model_label: model.model_label,
                        model_pk: model.model_pk,
                        entity,
                        entity_name: entityName,
                    };
                    revokeEntityFromModel(api, request, setProcessingEntityRevocation, setErrorRevokingEntity);
                }}
                loadingModelEntityAssignments={loadingModelEntityAssignments}
                processingEntityRevocation={processingEntityRevocation}
                errorLoadingModelEntityAssignments={errorLoadingModelEntityAssignments}
                errorRevokingEntity={errorRevokingEntity}
                numberOfColumns={modelEntityAssignmentsNumberOfColumns}
            />

            <ModelUnassignedEntitiesTable<E>
                entity={entity}
                modelUnassignedEntities={modelUnassignedEntities}
                searchTerm={modelUnassignedEntitiesSearchTerm}
                pageRequest={modelUnassignedEntitiesPageRequest}
                setSearchTerm={setModelUnassignedEntitiesSearchTerm}
                setPageRequest={setModelUnassignedEntitiesPageRequest}
                refreshPages={refreshPages}
                assignEntityToModel={async (entityName: string) => {
                    const request: AssignEntityToModelRequest = {
                        model_label: model.model_label,
                        model_pk: model.model_pk,
                        entity,
                        entity_name: entityName,
                    };
                    assignEntityToModel(api, request, setProcessingEntityAssignment, setErrorAssigningEntity);
                }}
                loadingModelUnassignedEntities={loadingModelUnassignedEntities}
                processingEntityAssignment={processingEntityAssignment}
                errorLoadingModelUnassignedEntities={errorLoadingModelUnassignedEntities}
                errorAssigningEntity={errorAssigningEntity}
                numberOfColumns={modelUnassignedEntitiesNumberOfColumns}
                entitySupported={modelEntitySupport[entity]}
            />
        </div>
    );
}
