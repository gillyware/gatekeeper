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
    type DeactivateEntityRequest,
    type DeactivateEntityResponse,
    type DeleteEntityRequest,
    type DeleteEntityResponse,
    type EntityPageRequest,
    type EntityPageResponse,
    type ReactivateEntityRequest,
    type ReactivateEntityResponse,
    type ShowEntityRequest,
    type ShowEntityResponse,
    type StoreEntityRequest,
    type StoreEntityResponse,
    type TurnEntityOffByDefaultRequest,
    type TurnEntityOffByDefaultResponse,
    type TurnEntityOnByDefaultRequest,
    type TurnEntityOnByDefaultResponse,
    type UpdateEntityRequest,
    type UpdateEntityResponse,
} from '@/types/api/entity';
import {
    type AssignEntityToModelResponse,
    type GetModelEntitiesPageRequest,
    type LookupModelResponse,
    type ModelEntityAssignmentsPageResponse,
    type ModelEntityRequest,
    type ModelPageRequest,
    type ModelPageResponse,
    type ModelRequest,
    type ModelUnassignedEntitiesPageResponse,
    type RevokeEntityFromModelResponse,
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

            updatePermission: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    return axios.put(`/permissions/${data.id}`, { name: data.name });
                }) as Promise<UpdateEntityResponse<GatekeeperPermission>>;
            },

            deactivatePermission: async (data: DeactivateEntityRequest): Promise<DeactivateEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    return axios.patch(`/permissions/${data.id}/deactivate`);
                }) as Promise<DeactivateEntityResponse<GatekeeperPermission>>;
            },

            reactivatePermission: async (data: ReactivateEntityRequest): Promise<ReactivateEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    return axios.patch(`/permissions/${data.id}/reactivate`);
                }) as Promise<ReactivateEntityResponse<GatekeeperPermission>>;
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

            updateRole: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    return axios.put(`/roles/${data.id}`, { name: data.name });
                }) as Promise<UpdateEntityResponse<GatekeeperRole>>;
            },

            deactivateRole: async (data: DeactivateEntityRequest): Promise<DeactivateEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    return axios.patch(`/roles/${data.id}/deactivate`);
                }) as Promise<DeactivateEntityResponse<GatekeeperRole>>;
            },

            reactivateRole: async (data: ReactivateEntityRequest): Promise<ReactivateEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    return axios.patch(`/roles/${data.id}/reactivate`);
                }) as Promise<ReactivateEntityResponse<GatekeeperRole>>;
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

            updateFeature: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    return axios.put(`/features/${data.id}`, { name: data.name });
                }) as Promise<UpdateEntityResponse<GatekeeperFeature>>;
            },

            turnFeatureOffByDefault: async (data: TurnEntityOffByDefaultRequest): Promise<TurnEntityOffByDefaultResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    return axios.patch(`/features/${data.id}/default-off`);
                }) as Promise<TurnEntityOffByDefaultResponse<GatekeeperFeature>>;
            },

            turnFeatureOnByDefault: async (data: TurnEntityOnByDefaultRequest): Promise<TurnEntityOnByDefaultResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    return axios.patch(`/features/${data.id}/default-on`);
                }) as Promise<TurnEntityOnByDefaultResponse<GatekeeperFeature>>;
            },

            deactivateFeature: async (data: DeactivateEntityRequest): Promise<DeactivateEntityResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    return axios.patch(`/features/${data.id}/deactivate`);
                }) as Promise<DeactivateEntityResponse<GatekeeperFeature>>;
            },

            reactivateFeature: async (data: ReactivateEntityRequest): Promise<ReactivateEntityResponse<GatekeeperFeature>> => {
                return handleResponse(() => {
                    return axios.patch(`/features/${data.id}/reactivate`);
                }) as Promise<ReactivateEntityResponse<GatekeeperFeature>>;
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

            updateTeam: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    return axios.put(`/teams/${data.id}`, { name: data.name });
                }) as Promise<UpdateEntityResponse<GatekeeperTeam>>;
            },

            deactivateTeam: async (data: DeactivateEntityRequest): Promise<DeactivateEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    return axios.patch(`/teams/${data.id}/deactivate`);
                }) as Promise<DeactivateEntityResponse<GatekeeperTeam>>;
            },

            reactivateTeam: async (data: ReactivateEntityRequest): Promise<ReactivateEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    return axios.patch(`/teams/${data.id}/reactivate`);
                }) as Promise<ReactivateEntityResponse<GatekeeperTeam>>;
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

            assignToModel: async (data: ModelEntityRequest): Promise<AssignEntityToModelResponse> => {
                const { entity_name } = data;

                return handleResponse(() => {
                    return axios.post(`/models/${data.model_label}/${data.model_pk}/entities/${data.entity}/assign`, { entity_name });
                }) as Promise<AssignEntityToModelResponse>;
            },

            revokeFromModel: async (data: ModelEntityRequest): Promise<RevokeEntityFromModelResponse> => {
                const { entity_name } = data;

                return handleResponse(() => {
                    return axios.delete(`/models/${data.model_label}/${data.model_pk}/entities/${data.entity}/revoke`, { data: { entity_name } });
                }) as Promise<RevokeEntityFromModelResponse>;
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
