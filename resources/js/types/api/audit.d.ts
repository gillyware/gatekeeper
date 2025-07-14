import { type GatekeeperResponse, type Pagination, type QueryOrder } from '@/types/api/index';
import { type AuditLog } from '@/types/models';

/**
 * ******************************************************************
 * Requests
 * ******************************************************************
 */

export interface AuditLogPageRequest {
    page: number;
    created_at_order?: QueryOrder;
}

/**
 * ******************************************************************
 * Responses
 * ******************************************************************
 */

export interface AuditLogPageResponse extends GatekeeperResponse {
    data?: Pagination<AuditLog>;
}
