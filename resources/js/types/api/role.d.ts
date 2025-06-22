import {
    DeactivateEntityRequest,
    DeleteEntityRequest,
    EntityPageRequest,
    GatekeeperResponse,
    Pagination,
    ReactivateEntityRequest,
    ShowEntityRequest,
    StoreEntityRequest,
    UpdateEntityRequest,
} from '@/types/api/index';
import { Role } from '@/types/models';

/**
 * ******************************************************************
 * Requests
 * ******************************************************************
 */

export interface RolePageRequest extends EntityPageRequest {}

export interface ShowRoleRequest extends ShowEntityRequest {}

export interface StoreRoleRequest extends StoreEntityRequest {}

export interface UpdateRoleRequest extends UpdateEntityRequest {}

export interface DeactivateRoleRequest extends DeactivateEntityRequest {}

export interface ReactivateRoleRequest extends ReactivateEntityRequest {}

export interface DeleteRoleRequest extends DeleteEntityRequest {}

/**
 * ******************************************************************
 * Responses
 * ******************************************************************
 */

export interface RolePageResponse extends GatekeeperResponse {
    data?: Pagination<Role>;
}

export interface ShowRoleResponse extends GatekeeperResponse {
    data?: Role;
}

export interface StoreRoleResponse extends GatekeeperResponse {
    data?: Role;
}

export interface UpdateRoleResponse extends GatekeeperResponse {
    data?: Role;
}

export interface DeactivateRoleResponse extends GatekeeperResponse {
    data?: Role;
}

export interface ReactivateRoleResponse extends GatekeeperResponse {
    data?: Role;
}

export interface DeleteRoleResponse extends GatekeeperResponse {
    data?: {};
}
