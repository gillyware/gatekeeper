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
import { Permission } from '@/types/models';

/**
 * ******************************************************************
 * Requests
 * ******************************************************************
 */

export interface PermissionPageRequest extends EntityPageRequest {}

export interface ShowPermissionRequest extends ShowEntityRequest {}

export interface StorePermissionRequest extends StoreEntityRequest {}

export interface UpdatePermissionRequest extends UpdateEntityRequest {}

export interface DeactivatePermissionRequest extends DeactivateEntityRequest {}

export interface ReactivatePermissionRequest extends ReactivateEntityRequest {}

export interface DeletePermissionRequest extends DeleteEntityRequest {}

/**
 * ******************************************************************
 * Responses
 * ******************************************************************
 */

export interface PermissionPageResponse extends GatekeeperResponse {
    data?: Pagination<Permission>;
}

export interface ShowPermissionResponse extends GatekeeperResponse {
    data?: Permission;
}

export interface StorePermissionResponse extends GatekeeperResponse {
    data?: Permission;
}

export interface UpdatePermissionResponse extends GatekeeperResponse {
    data?: Permission;
}

export interface DeactivatePermissionResponse extends GatekeeperResponse {
    data?: Permission;
}

export interface ReactivatePermissionResponse extends GatekeeperResponse {
    data?: Permission;
}

export interface DeletePermissionResponse extends GatekeeperResponse {
    data?: {};
}
