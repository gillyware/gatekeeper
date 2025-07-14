import { type GatekeeperResponse, type QueryOrder } from '@/types/api/index';
import { type GatekeeperEntity, type GatekeeperEntityModelMap } from '@/types/index';

export type EntityFieldName = 'name';
export type EntityFieldIsActive = 'is_active';
export type OrderableEntityField = EntityFieldName | EntityFieldIsActive;

/**
 * ******************************************************************
 * Requests
 * ******************************************************************
 */

export interface EntityPageRequest {
    page: number;
    search_term: string;
    prioritized_attribute: OrderableEntityField;
    name_order: QueryOrder;
    is_active_order: QueryOrder;
}

export interface ShowEntityRequest {
    id: number | string;
}

export interface StoreEntityRequest {
    name: string;
}

export interface UpdateEntityRequest {
    id: number | string;
    name: string;
}

export interface DeactivateEntityRequest {
    id: number | string;
}

export interface ReactivateEntityRequest {
    id: number | string;
}

export interface DeleteEntityRequest {
    id: number | string;
}

/**
 * ******************************************************************
 * Responses
 * ******************************************************************
 */

export interface EntityPageResponse<E extends GatekeeperEntity> extends GatekeeperResponse {
    data?: Pagination<GatekeeperEntityModelMap[E]>;
}

export interface ShowEntityResponse<E extends GatekeeperEntity> extends GatekeeperResponse {
    data?: GatekeeperEntityModelMap[E];
}

export interface StoreEntityResponse<E extends GatekeeperEntity> extends GatekeeperResponse {
    data?: GatekeeperEntityModelMap[E];
}

export interface UpdateEntityResponse<E extends GatekeeperEntity> extends GatekeeperResponse {
    data?: GatekeeperEntityModelMap[E];
}

export interface DeactivateEntityResponse<E extends GatekeeperEntity> extends GatekeeperResponse {
    data?: GatekeeperEntityModelMap[E];
}

export interface ReactivateEntityResponse<E extends GatekeeperEntity> extends GatekeeperResponse {
    data?: GatekeeperEntityModelMap[E];
}

export interface DeleteEntityResponse extends GatekeeperResponse {
    data?: {};
}
