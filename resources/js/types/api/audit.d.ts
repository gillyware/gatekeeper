import { GatekeeperResponse, Pagination } from '@/types/api/index';
import { AuditLog } from '@/types/models';

/**
 * ******************************************************************
 * Requests
 * ******************************************************************
 */

export interface AuditLogPageRequest {
    page: number;
    created_at_order?: 'asc' | 'desc';
}

/**
 * ******************************************************************
 * Responses
 * ******************************************************************
 */

export interface AuditLogPageResponse extends GatekeeperResponse {
    data?: Pagination<AuditLog>;
}
