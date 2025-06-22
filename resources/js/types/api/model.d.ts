import { GatekeeperEntity, GatekeeperEntityAssignmentMap, GatekeeperEntityModelMap } from '@/types';
import { GatekeeperResponse, Pagination } from '@/types/api/index';
import { Permission, Role, Team } from '@/types/models';

export interface ConfiguredModelMetadata {
    model_label: string;
    searchable: { [key: string]: string };
    displayable: { [key: string]: string };
    has_permissions: boolean;
    has_roles: boolean;
    has_teams: boolean;
}

export interface ConfiguredModelSearchResult extends ConfiguredModelMetadata {
    model_pk: string | number;
    display: { [key: string]: any };
}

export interface ConfiguredModelSearchByPermissionResult extends ConfiguredModelSearchResult {
    permission: Permission;
}

export interface ConfiguredModelSearchByRoleResult extends ConfiguredModelSearchResult {
    role: Role;
}

export interface ConfiguredModelSearchByTeamResult extends ConfiguredModelSearchResult {
    team: Team;
}

export interface ConfiguredModel extends ConfiguredModelSearchResult {
    direct_permissions: Permission[];
    direct_roles: Role[];
    direct_teams: Team[];
}

/**
 * ******************************************************************
 * Requests
 * ******************************************************************
 */

export interface ModelRequest {
    model_label: string;
    model_pk: string | number;
}

export interface SearchModelsRequest {
    model_label: string;
    search_term: string;
}

export interface SearchEntityAssignmentsForModelRequest extends ModelRequest, SearchModelsRequest {
    entity: GatekeeperEntity;
    page: number;
}

export interface SearchUnassignedEntitiesForModelRequest extends ModelRequest, SearchModelsRequest {
    entity: GatekeeperEntity;
    page: number;
}

export interface ModelEntityRequest extends ModelRequest {
    entity: GatekeeperEntity;
    entity_name: string;
}

export interface LookupModelRequest extends ModelRequest {}

export interface AssignEntityToModelRequest extends ModelEntityRequest {}

export interface RevokeEntityFromModelRequest extends ModelEntityRequest {}

/**
 * ******************************************************************
 * Responses
 * ******************************************************************
 */

export interface GetConfiguredModelsResponse extends GatekeeperResponse {
    data?: ConfiguredModelMetadata[];
}

export interface SearchModelsResponse extends GatekeeperResponse {
    data?: ConfiguredModelSearchResult[];
}

export interface SearchEntityAssignmentsForModelResponse<E extends GatekeeperEntity> extends GatekeeperResponse {
    data?: Pagination<GatekeeperEntityAssignmentMap[E]>;
}

export interface SearchUnassignedEntitiesForModelResponse<E extends GatekeeperEntity> extends GatekeeperResponse {
    data?: Pagination<GatekeeperEntityModelMap[E]>;
}

export interface LookupModelResponse extends GatekeeperResponse {
    data?: ConfiguredModel;
}

export interface AssignEntityToModelResponse extends GatekeeperResponse {
    data?: { message: string };
}

export interface RevokeEntityFromModelResponse extends GatekeeperResponse {
    data?: { message: string };
}
