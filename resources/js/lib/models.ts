import { useApi } from '@/lib/api';
import { apiText } from '@/lib/lang/en/api';
import { manageModelText, type ModelEntitySupportText } from '@/lib/lang/en/model/manage';
import { type GatekeeperConfig, type GatekeeperEntity, type GatekeeperEntityModelMap, type GatekeeperModelEntityAssignmentMap } from '@/types';
import { type GatekeeperErrors, type Pagination } from '@/types/api';
import {
    type ConfiguredModel,
    type ConfiguredModelMetadata,
    type ConfiguredModelSearchResult,
    type GetModelEntitiesPageRequest,
    type ModelEntityRequest,
    type ModelEntitySupport,
    type ModelPageRequest,
    type ModelRequest,
} from '@/types/api/model';

export async function getModels(
    api: ReturnType<typeof useApi>,
    request: ModelPageRequest,
    setModels: (models: ConfiguredModelSearchResult[]) => void,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setLoading(true);
    setError(null);

    const response = await api.getModels(request);

    if (response.status >= 400) {
        const errors: GatekeeperErrors = response.errors as GatekeeperErrors;
        setError(errors['general'] || apiText.models.getPageError);
        setModels([]);
        setLoading(false);
        return;
    }

    const models = response.data as ConfiguredModelSearchResult[];
    setModels(models);
    setLoading(false);
}

export async function getModel(
    api: ReturnType<typeof useApi>,
    params: ModelRequest,
    setModel: (model: ConfiguredModel | null) => void,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setLoading(true);
    setError(null);

    const response = await api.getModel(params);

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

export async function getEntityAssignmentsForModel<E extends GatekeeperEntity>(
    api: ReturnType<typeof useApi>,
    request: GetModelEntitiesPageRequest,
    setEntityAssignments: (entities: Pagination<GatekeeperModelEntityAssignmentMap[E]> | null) => void,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setLoading(true);
    setError(null);

    const response = await api.getEntityAssignmentsForModel(request);

    if (response.status >= 400) {
        setError(response.errors?.general || `Failed to fetch assigned ${request.entity}s.`);
        setEntityAssignments(null);
        setLoading(false);
        return;
    }

    const assignments = response.data as Pagination<GatekeeperModelEntityAssignmentMap[E]>;
    setEntityAssignments(assignments);
    setLoading(false);
}

export async function getUnassignedEntitiesForModel<E extends GatekeeperEntity>(
    api: ReturnType<typeof useApi>,
    request: GetModelEntitiesPageRequest,
    setModelUnassignedEntities: (entities: Pagination<GatekeeperEntityModelMap[E]> | null) => void,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setLoading(true);
    setError(null);

    const response = await api.getUnassignedEntitiesForModel(request);

    if (response.status >= 400) {
        setError(response.errors?.general || `Failed to fetch unassigned ${request.entity}s.`);
        setModelUnassignedEntities(null);
        setLoading(false);
        return;
    }

    const entities = response.data as Pagination<GatekeeperEntityModelMap[E]>;
    setModelUnassignedEntities(entities);
    setLoading(false);
}

export async function assignEntityToModel(
    api: ReturnType<typeof useApi>,
    data: ModelEntityRequest,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
): Promise<boolean> {
    setLoading(true);
    setError(null);

    const response = await api.assignToModel(data);

    if (response.status >= 400) {
        setError(response.errors?.general || `Failed to assign ${data.entity} '${data.entity_name}'.`);
        setLoading(false);
        return false;
    }

    setLoading(false);
    return true;
}

export async function revokeEntityFromModel(
    api: ReturnType<typeof useApi>,
    data: ModelEntityRequest,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
): Promise<boolean> {
    setLoading(true);
    setError(null);

    const response = await api.revokeFromModel(data);

    if (response.status >= 400) {
        setError(response.errors?.general || `Failed to revoke ${data.entity} '${data.entity_name}'.`);
        setLoading(false);
        return false;
    }

    setLoading(false);
    return true;
}

export function getEntitySupportForModel(config: GatekeeperConfig, model: ConfiguredModelMetadata): ModelEntitySupport {
    const result: ModelEntitySupport = {
        permission: { supported: true },
        role: { supported: true },
        feature: { supported: true },
        team: { supported: true },
    };

    const permissionsSupported = model.has_permissions && !model.is_permission;
    const rolesSupported = config.roles_enabled && model.has_roles && !model.is_role && !model.is_feature && !model.is_permission;
    const featuresSupported = config.features_enabled && model.has_features && !model.is_feature && !model.is_role && !model.is_permission;
    const teamsSupported = config.teams_enabled && model.has_teams && !model.is_team && !model.is_role && !model.is_permission;

    const language: ModelEntitySupportText = manageModelText.modelSummaryText.entitySupportText;

    if (!permissionsSupported) {
        result.permission.supported = false;
        result.permission.reason = model.is_permission ? language.permission.isPermission : language.permission.missingTrait;
    }

    if (!rolesSupported) {
        result.role.supported = false;
        result.role.reason = model.is_role
            ? language.role.isRole
            : model.is_feature
              ? language.role.isFeature
              : model.is_permission
                ? language.role.isPermission
                : !config.roles_enabled
                  ? language.role.featureDisabled
                  : language.role.missingTrait;
    }

    if (!featuresSupported) {
        result.feature.supported = false;
        result.feature.reason = model.is_feature
            ? language.role.isFeature
            : model.is_role
              ? language.feature.isRole
              : model.is_permission
                ? language.feature.isPermission
                : !config.features_enabled
                  ? language.feature.featureDisabled
                  : language.feature.missingTrait;
    }

    if (!teamsSupported) {
        result.team.supported = false;
        result.team.reason = model.is_team
            ? language.team.isTeam
            : model.is_role
              ? language.team.isRole
              : model.is_permission
                ? language.team.isPermission
                : !config.teams_enabled
                  ? language.team.featureDisabled
                  : language.team.missingTrait;
    }

    return result;
}
