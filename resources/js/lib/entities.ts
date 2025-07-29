import { type EntityFormType } from '@/components/entity/EntityForm';
import { useApi } from '@/lib/api';
import { apiText } from '@/lib/lang/en/api';
import { flattenStrings } from '@/lib/utils';
import { type GatekeeperConfig, type GatekeeperEntity, type GatekeeperEntityModelMap } from '@/types';
import { type GatekeeperErrors, type Pagination } from '@/types/api';
import { type EntityPageRequest, type ShowEntityRequest } from '@/types/api/entity';
import { type ConfiguredModelMetadata } from '@/types/api/model';

export function getModelMetadataForEntity(config: GatekeeperConfig, entity: GatekeeperEntity): ConfiguredModelMetadata | null {
    const model = config.models.find(
        (m) =>
            (m.is_permission && entity === 'permission') ||
            (m.is_role && entity === 'role') ||
            (m.is_feature && entity === 'feature') ||
            (m.is_team && entity === 'team'),
    );

    return model || null;
}

export async function getEntities<E extends GatekeeperEntity>(
    api: ReturnType<typeof useApi>,
    entity: GatekeeperEntity,
    request: EntityPageRequest,
    setEntities: (paginator: Pagination<GatekeeperEntityModelMap[E]> | null) => void,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setLoading(true);
    setError(null);

    const getPage = {
        permission: () => api.getPermissions(request),
        role: () => api.getRoles(request),
        feature: () => api.getFeatures(request),
        team: () => api.getTeams(request),
    };

    const response = await getPage[entity]();

    if (response.status >= 400) {
        const errors: GatekeeperErrors = response.errors as GatekeeperErrors;
        setError(errors['general'] || apiText.entities[entity].getPageError);
        setEntities(null);
        setLoading(false);
        return;
    }

    const entities = response.data as Pagination<GatekeeperEntityModelMap[E]>;
    setEntities(entities);
    setLoading(false);
}

export async function getEntity<E extends GatekeeperEntity>(
    api: ReturnType<typeof useApi>,
    entity: GatekeeperEntity,
    request: ShowEntityRequest,
    setEntity: (entity: GatekeeperEntityModelMap[E] | null) => void,
    setLoading: (loading: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setLoading(true);
    setError(null);

    const getEntity = {
        permission: () => api.getPermission(request),
        role: () => api.getRole(request),
        feature: () => api.getFeature(request),
        team: () => api.getTeam(request),
    };

    const response = await getEntity[entity]();

    if (response.status >= 400) {
        const errors: GatekeeperErrors = response.errors as GatekeeperErrors;
        setError(errors['general'] || apiText.entities[entity].getOneError);
        setEntity(null);
        setLoading(false);
        return;
    }

    const entityModel = response.data as GatekeeperEntityModelMap[E];
    setEntity(entityModel);
    setLoading(false);
}

export async function persistEntity<E extends GatekeeperEntity>(
    api: ReturnType<typeof useApi>,
    entity: GatekeeperEntity,
    formType: EntityFormType,
    entityId: number,
    entityName: string,
    onSuccess: (entityModel: GatekeeperEntityModelMap[E]) => void,
    setProcessing: (processing: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setProcessing(true);
    setError(null);

    const name = entityName.trim();

    const persist = {
        create: {
            permission: () => api.storePermission({ name }),
            role: () => api.storeRole({ name }),
            feature: () => api.storeFeature({ name }),
            team: () => api.storeTeam({ name }),
        },
        update: {
            permission: () => api.updatePermissionName({ id: entityId, name }),
            role: () => api.updateRoleName({ id: entityId, name }),
            feature: () => api.updateFeatureName({ id: entityId, name }),
            team: () => api.updateTeamName({ id: entityId, name }),
        },
    };

    const response = await persist[formType][entity]();

    if (response.status >= 400) {
        const errors: GatekeeperErrors = response.errors as GatekeeperErrors;
        setError(flattenStrings(errors['name']) || errors['general'] || apiText.entities[entity].persistError[formType]);
        setProcessing(false);
        return;
    }

    const entityModel = response.data as GatekeeperEntityModelMap[E];
    onSuccess(entityModel);
    setProcessing(false);
}

export async function revokeEntityDefaultGrant<E extends GatekeeperEntity>(
    api: ReturnType<typeof useApi>,
    entity: GatekeeperEntity,
    entityId: number,
    setEntityModel: (entityModel: GatekeeperEntityModelMap[E]) => void,
    setProcessing: (processing: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setProcessing(true);
    setError(null);

    const revokeDefaultGrant = {
        permission: () => api.revokePermissionDefaultGrant({ id: entityId }),
        role: () => api.revokeRoleDefaultGrant({ id: entityId }),
        feature: () => api.revokeFeatureDefaultGrant({ id: entityId }),
        team: () => api.revokeTeamDefaultGrant({ id: entityId }),
    };

    const response = await revokeDefaultGrant[entity]();

    if (response.status >= 400) {
        const errors: GatekeeperErrors = response.errors as GatekeeperErrors;
        setError(flattenStrings(errors['name']) || errors['general'] || apiText.entities[entity].revokeDefaultGrantError);
        setProcessing(false);
        return;
    }

    const entityModel = response.data as GatekeeperEntityModelMap[E];
    setEntityModel(entityModel);
    setProcessing(false);
}

export async function grantEntityByDefault<E extends GatekeeperEntity>(
    api: ReturnType<typeof useApi>,
    entity: GatekeeperEntity,
    entityId: number,
    setEntityModel: (entityModel: GatekeeperEntityModelMap[E]) => void,
    setProcessing: (processing: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setProcessing(true);
    setError(null);

    const grantByDefault = {
        permission: () => api.grantPermissionByDefault({ id: entityId }),
        role: () => api.grantRoleByDefault({ id: entityId }),
        feature: () => api.grantFeatureByDefault({ id: entityId }),
        team: () => api.grantTeamByDefault({ id: entityId }),
    };

    const response = await grantByDefault[entity]();

    if (response.status >= 400) {
        const errors: GatekeeperErrors = response.errors as GatekeeperErrors;
        setError(flattenStrings(errors['name']) || errors['general'] || apiText.entities[entity].grantByDefaultError);
        setProcessing(false);
        return;
    }

    const entityModel = response.data as GatekeeperEntityModelMap[E];
    setEntityModel(entityModel);
    setProcessing(false);
}

export async function deactivateEntity<E extends GatekeeperEntity>(
    api: ReturnType<typeof useApi>,
    entity: GatekeeperEntity,
    entityId: number,
    setEntityModel: (entityModel: GatekeeperEntityModelMap[E]) => void,
    setProcessing: (processing: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setProcessing(true);
    setError(null);

    const deactivate = {
        permission: () => api.deactivatePermission({ id: entityId }),
        role: () => api.deactivateRole({ id: entityId }),
        feature: () => api.deactivateFeature({ id: entityId }),
        team: () => api.deactivateTeam({ id: entityId }),
    };

    const response = await deactivate[entity]();

    if (response.status >= 400) {
        const errors: GatekeeperErrors = response.errors as GatekeeperErrors;
        setError(flattenStrings(errors['name']) || errors['general'] || apiText.entities[entity].deactivateError);
        setProcessing(false);
        return;
    }

    const entityModel = response.data as GatekeeperEntityModelMap[E];
    setEntityModel(entityModel);
    setProcessing(false);
}

export async function reactivateEntity<E extends GatekeeperEntity>(
    api: ReturnType<typeof useApi>,
    entity: GatekeeperEntity,
    entityId: number,
    setEntityModel: (entityModel: GatekeeperEntityModelMap[E]) => void,
    setProcessing: (processing: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setProcessing(true);
    setError(null);

    const deactivate = {
        permission: () => api.reactivatePermission({ id: entityId }),
        role: () => api.reactivateRole({ id: entityId }),
        feature: () => api.reactivateFeature({ id: entityId }),
        team: () => api.reactivateTeam({ id: entityId }),
    };

    const response = await deactivate[entity]();

    if (response.status >= 400) {
        const errors: GatekeeperErrors = response.errors as GatekeeperErrors;
        setError(flattenStrings(errors['name']) || errors['general'] || apiText.entities[entity].reactivateError);
        setProcessing(false);
        return;
    }

    const entityModel = response.data as GatekeeperEntityModelMap[E];
    setEntityModel(entityModel);
    setProcessing(false);
}

export async function deleteEntity(
    api: ReturnType<typeof useApi>,
    entity: GatekeeperEntity,
    entityId: number,
    onDelete: () => void,
    setProcessing: (processing: boolean) => void,
    setError: (error: string | null) => void,
): Promise<void> {
    setProcessing(true);
    setError(null);

    const deleteEntity = {
        permission: () => api.deletePermission({ id: entityId }),
        role: () => api.deleteRole({ id: entityId }),
        feature: () => api.deleteFeature({ id: entityId }),
        team: () => api.deleteTeam({ id: entityId }),
    };

    const response = await deleteEntity[entity]();

    if (response.status >= 400) {
        const errors: GatekeeperErrors = response.errors as GatekeeperErrors;
        setError(flattenStrings(errors['name']) || errors['general'] || apiText.entities[entity].deleteError);
        setProcessing(false);
        return;
    }

    onDelete();
}
