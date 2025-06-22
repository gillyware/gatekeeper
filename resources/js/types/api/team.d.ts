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
import { Team } from '@/types/models';

/**
 * ******************************************************************
 * Requests
 * ******************************************************************
 */

export interface TeamPageRequest extends EntityPageRequest {}

export interface ShowTeamRequest extends ShowEntityRequest {}

export interface StoreTeamRequest extends StoreEntityRequest {}

export interface UpdateTeamRequest extends UpdateEntityRequest {}

export interface DeactivateTeamRequest extends DeactivateEntityRequest {}

export interface ReactivateTeamRequest extends ReactivateEntityRequest {}

export interface DeleteTeamRequest extends DeleteEntityRequest {}

/**
 * ******************************************************************
 * Responses
 * ******************************************************************
 */

export interface TeamPageResponse extends GatekeeperResponse {
    data?: Pagination<Team>;
}

export interface ShowTeamResponse extends GatekeeperResponse {
    data?: Team;
}

export interface StoreTeamResponse extends GatekeeperResponse {
    data?: Team;
}

export interface UpdateTeamResponse extends GatekeeperResponse {
    data?: Team;
}

export interface DeactivateTeamResponse extends GatekeeperResponse {
    data?: Team;
}

export interface ReactivateTeamResponse extends GatekeeperResponse {
    data?: Team;
}

export interface DeleteTeamResponse extends GatekeeperResponse {
    data?: {};
}
