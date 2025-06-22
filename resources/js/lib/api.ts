import { useAxios } from '@/lib/axios';
import { GatekeeperEntity, GatekeeperEntityAssignmentMap } from '@/types';
import { GatekeeperError, GatekeeperResponse } from '@/types/api';
import { AuditLogPageRequest, AuditLogPageResponse } from '@/types/api/audit';
import {
    AssignEntityToModelRequest,
    AssignEntityToModelResponse,
    GetConfiguredModelsResponse,
    LookupModelRequest,
    LookupModelResponse,
    RevokeEntityFromModelRequest,
    RevokeEntityFromModelResponse,
    SearchEntityAssignmentsForModelRequest,
    SearchEntityAssignmentsForModelResponse,
    SearchModelsRequest,
    SearchModelsResponse,
    SearchUnassignedEntitiesForModelRequest,
    SearchUnassignedEntitiesForModelResponse,
} from '@/types/api/model';
import {
    DeactivatePermissionRequest,
    DeactivatePermissionResponse,
    DeletePermissionRequest,
    DeletePermissionResponse,
    PermissionPageRequest,
    PermissionPageResponse,
    ReactivatePermissionRequest,
    ReactivatePermissionResponse,
    ShowPermissionRequest,
    ShowPermissionResponse,
    StorePermissionRequest,
    StorePermissionResponse,
    UpdatePermissionRequest,
    UpdatePermissionResponse,
} from '@/types/api/permission';
import {
    DeactivateRoleRequest,
    DeactivateRoleResponse,
    DeleteRoleRequest,
    DeleteRoleResponse,
    ReactivateRoleRequest,
    ReactivateRoleResponse,
    RolePageRequest,
    RolePageResponse,
    ShowRoleRequest,
    ShowRoleResponse,
    StoreRoleRequest,
    StoreRoleResponse,
    UpdateRoleRequest,
    UpdateRoleResponse,
} from '@/types/api/role';
import {
    DeactivateTeamRequest,
    DeactivateTeamResponse,
    DeleteTeamRequest,
    DeleteTeamResponse,
    ReactivateTeamRequest,
    ReactivateTeamResponse,
    ShowTeamRequest,
    ShowTeamResponse,
    StoreTeamRequest,
    StoreTeamResponse,
    TeamPageRequest,
    TeamPageResponse,
    UpdateTeamRequest,
    UpdateTeamResponse,
} from '@/types/api/team';
import { AxiosError, AxiosResponse } from 'axios';
import { useMemo } from 'react';

export type FormResponse = {
    status: number;
    errors: Record<string, string> | {};
};

export function useApi() {
    const axios = useAxios();

    const api = useMemo(
        () => ({
            getPermissions: async (params: PermissionPageRequest): Promise<PermissionPageResponse> => {
                return handleResponse(() => {
                    return axios.get('/permissions', { params });
                });
            },

            getPermission: async (params: ShowPermissionRequest): Promise<ShowPermissionResponse> => {
                return handleResponse(() => {
                    return axios.get(`/permissions/${params.id}`);
                });
            },

            storePermission: async (data: StorePermissionRequest): Promise<StorePermissionResponse> => {
                return handleResponse(() => {
                    return axios.post('/permissions', data);
                });
            },

            updatePermission: async (data: UpdatePermissionRequest): Promise<UpdatePermissionResponse> => {
                return handleResponse(() => {
                    return axios.put(`/permissions/${data.id}`, { name: data.name });
                });
            },

            deactivatePermission: async (data: DeactivatePermissionRequest): Promise<DeactivatePermissionResponse> => {
                return handleResponse(() => {
                    return axios.patch(`/permissions/${data.id}/deactivate`);
                });
            },

            reactivatePermission: async (data: ReactivatePermissionRequest): Promise<ReactivatePermissionResponse> => {
                return handleResponse(() => {
                    return axios.patch(`/permissions/${data.id}/reactivate`);
                });
            },

            deletePermission: async (data: DeletePermissionRequest): Promise<DeletePermissionResponse> => {
                return handleResponse(() => {
                    return axios.delete(`/permissions/${data.id}`);
                });
            },

            getRoles: async (params: RolePageRequest): Promise<RolePageResponse> => {
                return handleResponse(() => {
                    return axios.get('/roles', { params });
                });
            },

            getRole: async (params: ShowRoleRequest): Promise<ShowRoleResponse> => {
                return handleResponse(() => {
                    return axios.get(`/roles/${params.id}`);
                });
            },

            storeRole: async (data: StoreRoleRequest): Promise<StoreRoleResponse> => {
                return handleResponse(() => {
                    return axios.post('/roles', data);
                });
            },

            updateRole: async (data: UpdateRoleRequest): Promise<UpdateRoleResponse> => {
                return handleResponse(() => {
                    return axios.put(`/roles/${data.id}`, { name: data.name });
                });
            },

            deactivateRole: async (data: DeactivateRoleRequest): Promise<DeactivateRoleResponse> => {
                return handleResponse(() => {
                    return axios.patch(`/roles/${data.id}/deactivate`);
                });
            },

            reactivateRole: async (data: ReactivateRoleRequest): Promise<ReactivateRoleResponse> => {
                return handleResponse(() => {
                    return axios.patch(`/roles/${data.id}/reactivate`);
                });
            },

            deleteRole: async (data: DeleteRoleRequest): Promise<DeleteRoleResponse> => {
                return handleResponse(() => {
                    return axios.delete(`/roles/${data.id}`);
                });
            },

            getTeams: async (params: TeamPageRequest): Promise<TeamPageResponse> => {
                return handleResponse(() => {
                    return axios.get('/teams', { params });
                });
            },

            getTeam: async (params: ShowTeamRequest): Promise<ShowTeamResponse> => {
                return handleResponse(() => {
                    return axios.get(`/teams/${params.id}`);
                });
            },

            storeTeam: async (data: StoreTeamRequest): Promise<StoreTeamResponse> => {
                return handleResponse(() => {
                    return axios.post('/teams', data);
                });
            },

            updateTeam: async (data: UpdateTeamRequest): Promise<UpdateTeamResponse> => {
                return handleResponse(() => {
                    return axios.put(`/teams/${data.id}`, { name: data.name });
                });
            },

            deactivateTeam: async (data: DeactivateTeamRequest): Promise<DeactivateTeamResponse> => {
                return handleResponse(() => {
                    return axios.patch(`/teams/${data.id}/deactivate`);
                });
            },

            reactivateTeam: async (data: ReactivateTeamRequest): Promise<ReactivateTeamResponse> => {
                return handleResponse(() => {
                    return axios.patch(`/teams/${data.id}/reactivate`);
                });
            },

            deleteTeam: async (data: DeleteTeamRequest): Promise<DeleteTeamResponse> => {
                return handleResponse(() => {
                    return axios.delete(`/teams/${data.id}`);
                });
            },

            getConfiguredModels: async (): Promise<GetConfiguredModelsResponse> => {
                return handleResponse(() => {
                    return axios.get('/models/configured');
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

            getAuditLogs: async (params: AuditLogPageRequest): Promise<AuditLogPageResponse> => {
                return handleResponse(() => {
                    return axios.get('/audit-logs', { params });
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
        console.error('Unexpected error:', error);
        return {
            status: 500,
            errors: {
                general: 'An unexpected error occurred.',
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
                general: e.message || 'Bad request.',
            },
        };
    }

    return {
        status: status,
        errors: {
            general: 'An unexpected error occurred.',
        },
    };
}
