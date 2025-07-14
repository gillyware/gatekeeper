import { useAxios } from '@/lib/axios';
import { apiText } from '@/lib/lang/en/api';
import { GatekeeperPermission, GatekeeperRole, GatekeeperTeam, type GatekeeperEntity, type GatekeeperEntityAssignmentMap } from '@/types';
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
    type UpdateEntityRequest,
    type UpdateEntityResponse,
} from '@/types/api/entity';
import {
    type AssignEntityToModelRequest,
    type AssignEntityToModelResponse,
    type LookupModelRequest,
    type LookupModelResponse,
    type RevokeEntityFromModelRequest,
    type RevokeEntityFromModelResponse,
    type SearchEntityAssignmentsForModelRequest,
    type SearchEntityAssignmentsForModelResponse,
    type SearchModelsRequest,
    type SearchModelsResponse,
    type SearchUnassignedEntitiesForModelRequest,
    type SearchUnassignedEntitiesForModelResponse,
} from '@/types/api/model';
import { type AxiosError, type AxiosResponse } from 'axios';
import { useMemo } from 'react';

export type FormResponse = {
    status: number;
    errors: Record<string, string> | {};
};

export function useApi() {
    const axios = useAxios();

    const api = useMemo(
        () => ({
            getPermissions: async (params: EntityPageRequest): Promise<EntityPageResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    return axios.get('/permissions', { params });
                });
            },

            getPermission: async (params: ShowEntityRequest): Promise<ShowEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    return axios.get(`/permissions/${params.id}`);
                });
            },

            storePermission: async (data: StoreEntityRequest): Promise<StoreEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    return axios.post('/permissions', data);
                });
            },

            updatePermission: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    return axios.put(`/permissions/${data.id}`, { name: data.name });
                });
            },

            deactivatePermission: async (data: DeactivateEntityRequest): Promise<DeactivateEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    return axios.patch(`/permissions/${data.id}/deactivate`);
                });
            },

            reactivatePermission: async (data: ReactivateEntityRequest): Promise<ReactivateEntityResponse<GatekeeperPermission>> => {
                return handleResponse(() => {
                    return axios.patch(`/permissions/${data.id}/reactivate`);
                });
            },

            deletePermission: async (data: DeleteEntityRequest): Promise<DeleteEntityResponse> => {
                return handleResponse(() => {
                    return axios.delete(`/permissions/${data.id}`);
                });
            },

            getRoles: async (params: EntityPageRequest): Promise<EntityPageResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    return axios.get('/roles', { params });
                });
            },

            getRole: async (params: ShowEntityRequest): Promise<ShowEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    return axios.get(`/roles/${params.id}`);
                });
            },

            storeRole: async (data: StoreEntityRequest): Promise<StoreEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    return axios.post('/roles', data);
                });
            },

            updateRole: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    return axios.put(`/roles/${data.id}`, { name: data.name });
                });
            },

            deactivateRole: async (data: DeactivateEntityRequest): Promise<DeactivateEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    return axios.patch(`/roles/${data.id}/deactivate`);
                });
            },

            reactivateRole: async (data: ReactivateEntityRequest): Promise<ReactivateEntityResponse<GatekeeperRole>> => {
                return handleResponse(() => {
                    return axios.patch(`/roles/${data.id}/reactivate`);
                });
            },

            deleteRole: async (data: DeleteEntityRequest): Promise<DeleteEntityResponse> => {
                return handleResponse(() => {
                    return axios.delete(`/roles/${data.id}`);
                });
            },

            getTeams: async (params: EntityPageRequest): Promise<EntityPageResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    return axios.get('/teams', { params });
                });
            },

            getTeam: async (params: ShowEntityRequest): Promise<ShowEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    return axios.get(`/teams/${params.id}`);
                });
            },

            storeTeam: async (data: StoreEntityRequest): Promise<StoreEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    return axios.post('/teams', data);
                });
            },

            updateTeam: async (data: UpdateEntityRequest): Promise<UpdateEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    return axios.put(`/teams/${data.id}`, { name: data.name });
                });
            },

            deactivateTeam: async (data: DeactivateEntityRequest): Promise<DeactivateEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    return axios.patch(`/teams/${data.id}/deactivate`);
                });
            },

            reactivateTeam: async (data: ReactivateEntityRequest): Promise<ReactivateEntityResponse<GatekeeperTeam>> => {
                return handleResponse(() => {
                    return axios.patch(`/teams/${data.id}/reactivate`);
                });
            },

            deleteTeam: async (data: DeleteEntityRequest): Promise<DeleteEntityResponse> => {
                return handleResponse(() => {
                    return axios.delete(`/teams/${data.id}`);
                });
            },

            searchModels: async (data: SearchModelsRequest): Promise<SearchModelsResponse> => {
                return handleResponse(() => {
                    return axios.get('/models/search', { params: data });
                });
            },

            getEntityAssignmentsForModel: async <E extends GatekeeperEntity>(
                data: SearchEntityAssignmentsForModelRequest,
            ): Promise<SearchEntityAssignmentsForModelResponse<GatekeeperEntityAssignmentMap[E]>> => {
                return handleResponse(() => {
                    return axios.get('/models/search-entity-assignments-for-model', { params: data });
                });
            },

            getUnassignedEntitiesForModel: async <E extends GatekeeperEntity>(
                data: SearchUnassignedEntitiesForModelRequest,
            ): Promise<SearchUnassignedEntitiesForModelResponse<E>> => {
                return handleResponse(() => {
                    return axios.get('/models/search-unassigned-entities-for-model', { params: data });
                });
            },

            lookupModel: async (params: LookupModelRequest): Promise<LookupModelResponse> => {
                return handleResponse(() => {
                    return axios.get(`/models/lookup`, { params });
                });
            },

            assignToModel: async (data: AssignEntityToModelRequest): Promise<AssignEntityToModelResponse> => {
                return handleResponse(() => {
                    return axios.post('/models/assign', data);
                });
            },

            revokeFromModel: async (params: RevokeEntityFromModelRequest): Promise<RevokeEntityFromModelResponse> => {
                return handleResponse(() => {
                    return axios.delete('/models/revoke', { params });
                });
            },

            getAuditLog: async (params: AuditLogPageRequest): Promise<AuditLogPageResponse> => {
                return handleResponse(() => {
                    return axios.get('/audit-log', { params });
                });
            },
        }),
        [axios],
    );

    return api;
}

async function handleResponse(apiCall: () => Promise<AxiosResponse<any, any>>): Promise<GatekeeperResponse> {
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
        } catch {}

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
