import {
    type GatekeeperEntity,
    type GatekeeperEntityModelMap,
    type GatekeeperModelEntityAssignmentMap,
    type GatekeeperModelEntityDenialMap,
} from '@/types';
import { type GatekeeperResponse, type Pagination } from '@/types/api/index';

export interface ConfiguredModelMetadata {
    model_label: string;
    searchable: { column: string; label: string }[];
    displayable: { column: string; label: string; cli_width: number }[];
    is_permission: boolean;
    is_role: boolean;
    is_feature: boolean;
    is_team: boolean;
    has_permissions: boolean;
    has_roles: boolean;
    has_features: boolean;
    has_teams: boolean;
}

export type ModelEntitySupport = Record<GatekeeperEntity, EntitySupported>;

export interface EntitySupported {
    supported: boolean;
    reason?: string;
}

export interface ConfiguredModelSearchResult extends ConfiguredModelMetadata {
    model_pk: string | number;
    display: { [key: string]: unknown };
}

export interface PermissionSource {
    type: 'default' | 'direct' | 'role' | 'feature' | 'team';
    role?: string;
    feature?: string;
    team?: string;
}

export interface RoleSource {
    type: 'default' | 'direct' | 'team';
    team?: string;
}

export interface FeatureSource {
    type: 'default' | 'direct' | 'team';
    team?: string;
}

export interface TeamSource {
    type: 'default' | 'direct';
}

export interface VerbosePermissions {
    name: string;
    sources: PermissionSource[];
}

export interface VerboseRoles {
    name: string;
    sources: RoleSource[];
}

export interface VerboseFeatures {
    name: string;
    sources: FeatureSource[];
}

export interface VerboseTeams {
    name: string;
    sources: TeamSource[];
}

export interface AccessSources {
    permissions: VerbosePermissions[];
    roles: VerboseRoles[];
    features: VerboseFeatures[];
    teams: VerboseTeams[];
    direct_permissions_count: number;
    direct_roles_count: number;
    direct_features_count: number;
    direct_teams_count: number;
}

export interface ConfiguredModel extends ConfiguredModelSearchResult {
    access_sources: AccessSources;
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

export interface ModelPageRequest {
    model_label: string;
    search_term: string;
}

export interface GetModelEntitiesPageRequest extends ModelRequest, ModelPageRequest {
    entity: GatekeeperEntity;
    page: number;
}

export interface ModelEntityRequest extends ModelRequest {
    entity: GatekeeperEntity;
    entity_name: string;
}

/**
 * ******************************************************************
 * Responses
 * ******************************************************************
 */

export interface ModelPageResponse extends GatekeeperResponse {
    data?: ConfiguredModelSearchResult[];
}

export interface ModelEntityAssignmentsPageResponse<E extends GatekeeperEntity> extends GatekeeperResponse {
    data?: Pagination<GatekeeperModelEntityAssignmentMap[E]>;
}

export interface ModelUnassignedEntitiesPageResponse<E extends GatekeeperEntity> extends GatekeeperResponse {
    data?: Pagination<GatekeeperEntityModelMap[E]>;
}

export interface ModelDeniedEntitiesPageResponse<E extends GatekeeperEntity> extends GatekeeperResponse {
    data?: Pagination<GatekeeperModelEntityDenialMap[E]>;
}

export interface LookupModelResponse extends GatekeeperResponse {
    data?: ConfiguredModel;
}

export interface ModelEntityResponse extends GatekeeperResponse {
    data?: { message: string };
}
