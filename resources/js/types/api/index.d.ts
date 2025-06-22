export interface Pagination<T> {
    current_page: number;
    data: T[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
}

export interface GatekeeperError {
    message: string;
}

/**
 * ******************************************************************
 * Requests
 * ******************************************************************
 */

export interface EntityPageRequest {
    page: number;
    important_attribute?: 'name' | 'is_active';
    name_order?: 'asc' | 'desc';
    is_active_order?: 'asc' | 'desc';
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

export interface GatekeeperResponse {
    status: number;
    data?: any;
    errors?: {
        general?: string;
        [key: string]: string[] | string;
    };
}
