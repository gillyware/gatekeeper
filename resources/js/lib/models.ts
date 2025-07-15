import { useApi } from '@/lib/api';
import { manageModelText, type ModelEntitySupportText } from '@/lib/lang/en/model/manage';
import { type GatekeeperConfig, type GatekeeperEntity, type GatekeeperEntityAssignmentMap, type GatekeeperEntityModelMap } from '@/types';
import { type Pagination } from '@/types/api';
import {
    type AssignEntityToModelRequest,
    type ConfiguredModel,
    type ConfiguredModelMetadata,
    type LookupModelRequest,
    type ModelEntitySupport,
    type RevokeEntityFromModelRequest,
} from '@/types/api/model';

export function getEntitySupportForModel(config: GatekeeperConfig, model: ConfiguredModelMetadata): ModelEntitySupport {
    const result: ModelEntitySupport = {
        permission: { supported: true },
        role: { supported: true },
        team: { supported: true },
    };

    const permissionsSupported = model.has_permissions && !model.is_permission;
    const rolesSupported = config.roles_enabled && model.has_roles && !model.is_role && !model.is_permission;
    const teamsSupported = config.teams_enabled && model.has_teams && !model.is_team && !model.is_role && !model.is_permission;

    const language: ModelEntitySupportText = manageModelText.modelSummaryText.entitySupportText;

    if (!permissionsSupported) {
        result.permission.supported = false;
        result.permission.reason = model.is_role ? language.permission.isPermission : language.permission.missingTrait;
    }

    if (!rolesSupported) {
        result.role.supported = false;
        result.role.reason = !config.roles_enabled
            ? language.role.featureDisabled
            : model.is_role
              ? language.role.isRole
              : model.is_permission
                ? language.role.isPermission
                : language.role.missingTrait;
    }

    if (!teamsSupported) {
        result.team.supported = false;
        result.team.reason = !config.teams_enabled
            ? language.team.featureDisabled
            : model.is_team
              ? language.team.isTeam
              : model.is_role
                ? language.team.isRole
                : model.is_permission
                  ? language.team.isPermission
                  : language.team.missingTrait;
    }

    return result;
}

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
