import { useApi } from '@/lib/api';
import { type GatekeeperEntity, type GatekeeperEntityAssignmentMap, type GatekeeperEntityModelMap } from '@/types';
import { type Pagination } from '@/types/api';
import { type AssignEntityToModelRequest, type ConfiguredModel, type LookupModelRequest, type RevokeEntityFromModelRequest } from '@/types/api/model';

export async function fetchEntityAssignmentsForModel<E extends GatekeeperEntity>(
    api: ReturnType<typeof useApi>,
    modelLabel: string,
    modelPk: string | number,
    entity: E,
    entitySearchTerm: string,
    pageNumber: number,
    setEntityAssignments: (entities: Pagination<GatekeeperEntityAssignmentMap[E]> | null) => void,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setLoading(true);
    setError(null);

    const response = await api.getEntityAssignmentsForModel({
        model_label: modelLabel,
        model_pk: modelPk,
        entity: entity,
        search_term: entitySearchTerm,
        page: pageNumber,
    });

    if (response.status >= 400) {
        setError(response.errors?.general || `Failed to fetch assigned ${entity}s.`);
        setEntityAssignments(null);
        setLoading(false);
        return;
    }

    const assignments = response.data as Pagination<GatekeeperEntityAssignmentMap[E]>;
    setEntityAssignments(assignments);
    setLoading(false);
}

export async function fetchUnassignedEntitiesForModel<E extends GatekeeperEntity>(
    api: ReturnType<typeof useApi>,
    modelLabel: string,
    modelPk: string | number,
    entity: E,
    entitySearchTerm: string,
    pageNumber: number,
    setUnassignedEntities: (entities: Pagination<GatekeeperEntityModelMap[E]> | null) => void,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setLoading(true);
    setError(null);

    const response = await api.getUnassignedEntitiesForModel({
        model_label: modelLabel,
        model_pk: modelPk,
        entity: entity,
        search_term: entitySearchTerm,
        page: pageNumber,
    });

    if (response.status >= 400) {
        setError(response.errors?.general || `Failed to fetch unassigned ${entity}s.`);
        setUnassignedEntities(null);
        setLoading(false);
        return;
    }

    const entities = response.data as Pagination<GatekeeperEntityModelMap[E]>;
    setUnassignedEntities(entities);
    setLoading(false);
}

export async function fetchModel(
    api: ReturnType<typeof useApi>,
    params: LookupModelRequest,
    setModel: (model: ConfiguredModel | null) => void,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setLoading(true);
    setError(null);

    const response = await api.lookupModel(params);

    if (response.status >= 400) {
        setError(response.errors?.general || 'Failed to fetch model.');
        setModel(null);
        setLoading(false);
        return;
    }

    const model = response.data as ConfiguredModel;
    setModel(model);
    setLoading(false);
}

export async function assignEntityToModel(
    api: ReturnType<typeof useApi>,
    data: AssignEntityToModelRequest,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
) {
    setLoading(true);
    setError(null);

    const response = await api.assignToModel(data);

    if (response.status >= 400) {
        setError(response.errors?.general || `Failed to assign ${data.entity} '${data.entity_name}'.`);
        setLoading(false);
        return;
    }

    setLoading(false);
}

export async function revokeEntityFromModel(
    api: ReturnType<typeof useApi>,
    data: RevokeEntityFromModelRequest,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
) {
    setLoading(true);
    setError(null);

    const response = await api.revokeFromModel(data);

    if (response.status >= 400) {
        setError(response.errors?.general || `Failed to revoke ${data.entity} '${data.entity_name}'.`);
        setLoading(false);
        return;
    }

    setLoading(false);
}
