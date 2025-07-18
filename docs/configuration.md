# Configuration

- [Path](#path)
- [Timezone](#timezone)
- [Feature Flags](#features)
- [Tables](#tables)
- [Cache](#cache)
- [Models](#models)
- [Next Steps](#next-steps)

After installing Gatekeeper, you may configure it via the `config/gatekeeper.php` file.

<a name="path"></a>
## Path

**Key:** `path`

Defines the URI path where the Gatekeeper dashboard will be available (default: `gatekeeper`). Note that this will not affect the endpoints for Gatekeeper's internal API.

```php
'path' => env('GATEKEEPER_PATH', 'gatekeeper'),
```

<a name="timezone"></a>
## Timezone

**Key:** `timezone`

Specifies which timezone to display datetimes on the dashboard. This does not affect how the timezone is stored in the database. The display timezone defaults to `config('app.timezone')` if available, otherwise falls back to `'UTC'`.

```php
'timezone' => env('GATEKEEPER_TIMEZONE', config('app.timezone', 'UTC')),
```

<a name="features"></a>
## Feature Flags

**Key:** `features`

Determines which core Gatekeeper features are enabled:

```php
'features' => [
    'audit' => ['enabled' => true],
    'roles' => ['enabled' => true],
    'teams' => ['enabled' => false],
],
```

> [!WARNING]
> Gatekeeper ships with the `teams` feature disabled, so be sure to enable it if your application depends on it.

<a name="tables"></a>
## Tables

**Key:** `tables`

Specifies the database table names used by Gatekeeper. There are 3 tables for entities, 3 pivot tables to assign entities to models, and an audit log table.

```php
'tables' => [
    'permissions' => 'permissions',
    'roles' => 'roles',
    'teams' => 'teams',
    'model_has_permissions' => 'model_has_permissions',
    'model_has_roles' => 'model_has_roles',
    'model_has_teams' => 'model_has_teams',
    'audit_log' => 'gatekeeper_audit_log',
],
```

<a name="cache"></a>
## Cache

**Key:** `cache`

Determines whether Gatekeeper can use the application's cache, the string prefix applied to all Gatekeeper-related cache keys, and how long cached items will live (in seconds).

```php
'cache' => [
    'enabled' => env('GATEKEEPER_CACHE_ENABLED', true),
    'prefix' => env('GATEKEEPER_CACHE_PREFIX', 'gatekeeper'),
    'ttl' => env('GATEKEEPER_CACHE_TTL', 2 * 60 * 60),
],
```

<a name="models"></a>
## Models

**Key:** `models.manageable`

Indicates which models are manageable by Gatekeeper. For each model, you may specify 4 items:
1. `label` - A friendly name used for the model when referring to it on the dashboard and within the CLI.
2. `class` - The fully qualified name for the model.
3. `searchable` - Which database columns are searchable for the model along with a friendly name for the column.
4. `displayable` - Which database columns are displayable for the model along with a friendly name for the column and a size for displaying the column within the CLI.

```php
'models' => [
    'manageable' => [
        'user' => [
            'label' => 'User',
            'class' => \App\Models\User::class,
            'searchable' => [
                ['column' => 'id', 'label' => 'ID'],
                ['column' => 'name', 'label' => 'name'],
                ['column' => 'email', 'label' => 'email'],
            ],
            'displayable' => [
                ['column' => 'id', 'label' => 'ID', 'cli_width' => 10],
                ['column' => 'name', 'label' => 'Name', 'cli_width' => 25],
                ['column' => 'email', 'label' => 'Email', 'cli_width' => 35],
            ],
        ],
        ...
    ],
],
```

> [!WARNING]
> Only models listed under `models.manageable` will be available in the dashboard or via CLI commands.

<a name="next-steps"></a>
## Next Steps

Now that you've configured Gatekeeper, you can begin managing access using permissions:

[Permissions](usage/permissions.md)
