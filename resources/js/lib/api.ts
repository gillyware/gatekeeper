import { useAxios } from '@/lib/axios';
import { apiText } from '@/lib/lang/en/api';
import {
    type GatekeeperEntity,
    type GatekeeperFeature,
    type GatekeeperModelEntityAssignmentMap,
    type GatekeeperPermission,
    type GatekeeperRole,
    type GatekeeperTeam,
} from '@/types';
import { type GatekeeperError, type GatekeeperResponse } from '@/types/api';
import { type AuditLogPageRequest, type AuditLogPageResponse } from '@/types/api/audit';
import {
    type DeleteEntityRequest,
    type DeleteEntityResponse,
    type EntityPageRequest,
    type EntityPageResponse,
    type ShowEntityRequest,
    type ShowEntityResponse,
    type StoreEntityRequest,
    type StoreEntityResponse,
    type UpdateEntityPayload,
    type UpdateEntityRequest,
    type UpdateEntityResponse,
} from '@/types/api/entity';
import {
    type GetModelEntitiesPageRequest,
    type LookupModelResponse,
    type ModelDeniedEntitiesPageResponse,
    type ModelEntityAssignmentsPageResponse,
    type ModelEntityRequest,
    type ModelEntityResponse,
    type ModelPageRequest,
    type ModelPageResponse,
    type ModelRequest,
    type ModelUnassignedEntitiesPageResponse,
} from '@/types/api/model';
import { type AxiosError, type AxiosResponse } from 'axios';
import { useMemo } from 'react';

export type FormResponse = {
    status: number;
    errors: Record<string, string> | [];
};

export function useApi() {
    const axios = useAxios();

    const api = useMemo(
        () => ({
            getPermissions: async (params: EntityPageRequest): Promise<EntityPageResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    return axios.get('/permissions', { params });
                }) as Promise<EntityPageResponse<GatekeeperPermission>>;
            },

            getPermission: async (params: ShowEntityRequest): Promise<ShowEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    return axios.get(`/permissions/${params.id}`);
                }) as Promise<ShowEntityResponse<GatekeeperPermission>>;
            },

            storePermission: async (data: StoreEntityRequest): Promise<StoreEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    return axios.post('/permissions', data);
                }) as Promise<StoreEntityResponse<GatekeeperPermission>>;
            },

            updatePermissionName: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'name', value: data.name as string };
                    return axios.patch(`/permissions/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperPermission>>;
            },

            grantPermissionByDefault: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'default_grant', value: true };
                    return axios.patch(`/permissions/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperPermission>>;
            },

            revokePermissionDefaultGrant: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'default_grant', value: false };
                    return axios.patch(`/permissions/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperPermission>>;
            },

            deactivatePermission: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'status', value: false };
                    return axios.patch(`/permissions/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperPermission>>;
            },

            reactivatePermission: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'status', value: true };
                    return axios.patch(`/permissions/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperPermission>>;
            },

            deletePermission: async (data: DeleteEntityRequest): Promise<DeleteEntityResponse> => {
                return handleResponse(() => {
                    return axios.delete(`/permissions/${data.id}`);
                }) as Promise<DeleteEntityResponse>;
            },

            getRoles: async (params: EntityPageRequest): Promise<EntityPageResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    return axios.get('/roles', { params });
                }) as Promise<EntityPageResponse<GatekeeperRole>>;
            },

            getRole: async (params: ShowEntityRequest): Promise<ShowEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    return axios.get(`/roles/${params.id}`);
                }) as Promise<ShowEntityResponse<GatekeeperRole>>;
            },

            storeRole: async (data: StoreEntityRequest): Promise<StoreEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    return axios.post('/roles', data);
                }) as Promise<StoreEntityResponse<GatekeeperRole>>;
            },

            updateRoleName: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'name', value: data.name as string };
                    return axios.patch(`/roles/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperRole>>;
            },

            grantRoleByDefault: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'default_grant', value: true };
                    return axios.patch(`/roles/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperRole>>;
            },

            revokeRoleDefaultGrant: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'default_grant', value: false };
                    return axios.patch(`/roles/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperRole>>;
            },

            deactivateRole: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'status', value: false };
                    return axios.patch(`/roles/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperRole>>;
            },

            reactivateRole: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'status', value: true };
                    return axios.patch(`/roles/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperRole>>;
            },

            deleteRole: async (data: DeleteEntityRequest): Promise<DeleteEntityResponse> => {
                return handleResponse(() => {
                    return axios.delete(`/roles/${data.id}`);
                }) as Promise<DeleteEntityResponse>;
            },

            getFeatures: async (params: EntityPageRequest): Promise<EntityPageResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    return axios.get('/features', { params });
                }) as Promise<EntityPageResponse<GatekeeperFeature>>;
            },

            getFeature: async (params: ShowEntityRequest): Promise<ShowEntityResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    return axios.get(`/features/${params.id}`);
                }) as Promise<ShowEntityResponse<GatekeeperFeature>>;
            },

            storeFeature: async (data: StoreEntityRequest): Promise<StoreEntityResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    return axios.post('/features', data);
                }) as Promise<StoreEntityResponse<GatekeeperFeature>>;
            },

            updateFeatureName: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'name', value: data.name as string };
                    return axios.patch(`/features/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperFeature>>;
            },

            grantFeatureByDefault: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'default_grant', value: true };
                    return axios.patch(`/features/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperFeature>>;
            },

            revokeFeatureDefaultGrant: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'default_grant', value: false };
                    return axios.patch(`/features/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperFeature>>;
            },

            deactivateFeature: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'status', value: false };
                    return axios.patch(`/features/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperFeature>>;
            },

            reactivateFeature: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'status', value: true };
                    return axios.patch(`/features/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperFeature>>;
            },

            deleteFeature: async (data: DeleteEntityRequest): Promise<DeleteEntityResponse> => {
                return handleResponse(() => {
                    return axios.delete(`/features/${data.id}`);
                }) as Promise<DeleteEntityResponse>;
            },

            getTeams: async (params: EntityPageRequest): Promise<EntityPageResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    return axios.get('/teams', { params });
                }) as Promise<EntityPageResponse<GatekeeperTeam>>;
            },

            getTeam: async (params: ShowEntityRequest): Promise<ShowEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    return axios.get(`/teams/${params.id}`);
                }) as Promise<ShowEntityResponse<GatekeeperTeam>>;
            },

            storeTeam: async (data: StoreEntityRequest): Promise<StoreEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    return axios.post('/teams', data);
                }) as Promise<StoreEntityResponse<GatekeeperTeam>>;
            },

            updateTeamName: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'name', value: data.name as string };
                    return axios.patch(`/teams/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperTeam>>;
            },

            grantTeamByDefault: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'default_grant', value: true };
                    return axios.patch(`/teams/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperTeam>>;
            },

            revokeTeamDefaultGrant: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'default_grant', value: false };
                    return axios.patch(`/teams/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperTeam>>;
            },

            deactivateTeam: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'status', value: false };
                    return axios.patch(`/teams/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperTeam>>;
            },

            reactivateTeam: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    const payload: UpdateEntityPayload = { action: 'status', value: true };
                    return axios.patch(`/teams/${data.id}`, payload);
                }) as Promise<UpdateEntityResponse<GatekeeperTeam>>;
            },

            deleteTeam: async (data: DeleteEntityRequest): Promise<DeleteEntityResponse> => {
                return handleResponse(() => {
                    return axios.delete(`/teams/${data.id}`);
                }) as Promise<DeleteEntityResponse>;
            },

            getModels: async (data: ModelPageRequest): Promise<ModelPageResponse> => {
                return handleResponse(() => {
                    return axios.get('/models', { params: data });
                }) as Promise<ModelPageResponse>;
            },

            getModel: async (params: ModelRequest): Promise<LookupModelResponse> => {
                return handleResponse(() => {
                    return axios.get(`/models/${params.model_label}/${params.model_pk}`);
                }) as Promise<LookupModelResponse>;
            },

            getEntityAssignmentsForModel: async <E extends GatekeeperEntity>(
                params: GetModelEntitiesPageRequest,
            ): Promise<ModelEntityAssignmentsPageResponse<GatekeeperModelEntityAssignmentMap[E]>> => {
                const { model_label, model_pk, entity, ...filterParams } = params;

                return handleResponse(() => {
                    return axios.get(`/models/${model_label}/${model_pk}/entities/${entity}/assigned`, { params: filterParams });
                }) as Promise<ModelEntityAssignmentsPageResponse<GatekeeperModelEntityAssignmentMap[E]>>;
            },

            getUnassignedEntitiesForModel: async <E extends GatekeeperEntity>(
                params: GetModelEntitiesPageRequest,
            ): Promise<ModelUnassignedEntitiesPageResponse<E>> => {
                const { model_label, model_pk, entity, ...filterParams } = params;

                return handleResponse(() => {
                    return axios.get(`/models/${model_label}/${model_pk}/entities/${entity}/unassigned`, {
                        params: filterParams,
                    });
                }) as Promise<ModelUnassignedEntitiesPageResponse<E>>;
            },

            getDeniedEntitiesForModel: async <E extends GatekeeperEntity>(
                params: GetModelEntitiesPageRequest,
            ): Promise<ModelDeniedEntitiesPageResponse<E>> => {
                const { model_label, model_pk, entity, ...filterParams } = params;

                return handleResponse(() => {
                    return axios.get(`/models/${model_label}/${model_pk}/entities/${entity}/denied`, {
                        params: filterParams,
                    });
                }) as Promise<ModelDeniedEntitiesPageResponse<E>>;
            },

            assignToModel: async (data: ModelEntityRequest): Promise<ModelEntityResponse> => {
                const { entity_name } = data;

                return handleResponse(() => {
                    return axios.post(`/models/${data.model_label}/${data.model_pk}/entities/${data.entity}/assign`, { entity_name });
                }) as Promise<ModelEntityResponse>;
            },

            unassignFromModel: async (data: ModelEntityRequest): Promise<ModelEntityResponse> => {
                const { entity_name } = data;

                return handleResponse(() => {
                    return axios.delete(`/models/${data.model_label}/${data.model_pk}/entities/${data.entity}/unassign`, { data: { entity_name } });
                }) as Promise<ModelEntityResponse>;
            },

            denyFromModel: async (data: ModelEntityRequest): Promise<ModelEntityResponse> => {
                const { entity_name } = data;

                return handleResponse(() => {
                    return axios.post(`/models/${data.model_label}/${data.model_pk}/entities/${data.entity}/deny`, { entity_name });
                }) as Promise<ModelEntityResponse>;
            },

            undenyFromModel: async (data: ModelEntityRequest): Promise<ModelEntityResponse> => {
                const { entity_name } = data;

                return handleResponse(() => {
                    return axios.delete(`/models/${data.model_label}/${data.model_pk}/entities/${data.entity}/undeny`, { data: { entity_name } });
                }) as Promise<ModelEntityResponse>;
            },

            getAuditLog: async (params: AuditLogPageRequest): Promise<AuditLogPageResponse> => {
                return handleResponse(() => {
                    return axios.get('/audit-log', { params });
                }) as Promise<AuditLogPageResponse>;
            },
        }),
        [axios],
    );

    return api;
}

async function handleResponse(apiCall: () => Promise<AxiosResponse<unknown, unknown>>): Promise<GatekeeperResponse> {
    return apiCall()
        .then((response) => {
            return {
                status: response.status,
                data: response.data,
            };
        })
        .catch((error: AxiosError) => {
            return handleError(error);
        });
}

function handleError(error: AxiosError): GatekeeperResponse {
    const status = error.response?.status || 500;

    if (![400, 422].includes(status || 500)) {
        return {
            status: 500,
            errors: {
                general: apiText.unexpectedError,
            },
        };
    }

    const e = error.response?.data as GatekeeperError;

    if (status === 422) {
        let parsedErrors: Record<string, string> = {};

        try {
            if (typeof e.message === 'string') {
                const rawErrors = JSON.parse(e.message) as Record<string, string[]>;
                parsedErrors = Object.fromEntries(Object.entries(rawErrors).map(([key, value]) => [key, value.join(' ')]));
            }
        } catch (e) {
            console.error(e);
        }

        return {
            status: 422,
            errors: parsedErrors,
        };
    }

    if (status === 400) {
        return {
            status: 400,
            errors: {
                general: e.message || apiText.badRequest,
            },
        };
    }

    return {
        status: status,
        errors: {
            general: apiText.unexpectedError,
        },
    };
}
