<?php

namespace Gillyware\Gatekeeper\Constants;

class GatekeeperConfigDefault
{
    /**
     * ******************************************************************
     * Path
     * ******************************************************************
     */
    public const PATH = 'gatekeeper';

    /**
     * ******************************************************************
     * Features
     * ******************************************************************
     */
    public const FEATURES_AUDIT_ENABLED = true;

    public const FEATURES_ROLES_ENABLED = true;

    public const FEATURES_TEAMS_ENABLED = false;

    /**
     * ******************************************************************
     * Tables
     * ******************************************************************
     */
    public const TABLES_PERMISSIONS = 'permissions';

    public const TABLES_ROLES = 'roles';

    public const TABLES_TEAMS = 'teams';

    public const TABLES_MODEL_HAS_PERMISSIONS = 'model_has_permissions';

    public const TABLES_MODEL_HAS_ROLES = 'model_has_roles';

    public const TABLES_MODEL_HAS_TEAMS = 'model_has_teams';

    public const TABLES_AUDIT_LOGS = 'gatekeeper_audit_logs';

    /**
     * ******************************************************************
     * Cache
     * ******************************************************************
     */
    public const CACHE_PREFIX = 'gatekeeper';

    public const CACHE_TTL = 2 * 60 * 60;
}
