export interface GatekeeperError {
    message: string;
}

export interface GatekeeperErrors {
    general?: string;
    [key: string]: string[] | string;
}

export interface GatekeeperResponse {
    status: number;
    data?: any;
    errors?: GatekeeperErrors;
}

export interface Pagination<T> {
    current_page: number;
    data: T[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
}

export type QueryOrderAsc = 'asc';

export type QueryOrderDesc = 'desc';

export type QueryOrder = 'asc' | 'desc';
