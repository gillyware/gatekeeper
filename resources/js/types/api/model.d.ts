import { type GatekeeperEntity, type GatekeeperEntityAssignmentMap, type GatekeeperEntityModelMap } from '@/types';
import { type GatekeeperResponse, type Pagination } from '@/types/api/index';
import { type Permission, type Role, type Team } from '@/types/models';

export interface ConfiguredModelMetadata {
    model_label: string;
    searchable: { column: string; label: string }[];
    displayable: { column: string; label: string; cli_width: number }[];
    is_permission: boolean;
    is_role: boolean;
    is_team: boolean;
    has_permissions: boolean;
    has_roles: boolean;
    has_teams: boolean;
}

export type ModelEntitySupport = Record<
    GatekeeperEntity,
    {
        supported: boolean;
        reason?: string;
    }
>;

export interface ConfiguredModelSearchResult extends ConfiguredModelMetadata {
    model_pk: string | number;
    display: { [key: string]: any };
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
