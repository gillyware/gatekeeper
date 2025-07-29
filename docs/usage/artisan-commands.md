# Artisan Commands

- [Overview](#overview)
- [gatekeeper:list](#gatekeeperlist)
- [gatekeeper:permission](#gatekeeperpermission)
- [gatekeeper:role](#gatekeeperrole)
- [gatekeeper:feature](#gatekeeperfeature)
- [gatekeeper:team](#gatekeeperteam)
- [gatekeeper:clear](#gatekeeperclear)
- [Next Steps](#next-steps)

<a name="overview"></a>
## Overview

Gatekeeper provides several Artisan commands to help manage permissions, roles, features, teams, and assignments through the command line. These commands offer an interactive interface for common tasks such as creating, assigning, and unassigning entities.

<a name="gatekeeperlist"></a>
## `gatekeeper:list`

List all entities (permissions, roles, features, and teams) in your application.

This command displays the name, active status, default grant status, and timestamps for each entity, using your configured timezone.

### Usage

```bash
php artisan gatekeeper:list
```

By default, it will display all entities unless filtered with one or more options.

### Options

| Option          | Description           |
|-----------------|-----------------------|
| `--permissions` | Only show permissions |
| `--roles`       | Only show roles       |
| `--features`    | Only show features    |
| `--teams`       | Only show teams       |

You can combine options to show only the specific types you want.

### Example

List only permissions:

```bash
php artisan gatekeeper:list --permissions
```

List only roles and teams (no permissions):

```bash
php artisan gatekeeper:list --roles --teams
```

<a name="gatekeeperpermission"></a>
## `gatekeeper:permission`

Interactively manage Gatekeeper permissions via the CLI.

This command supports creating, updating names, granting by default, revoking default grants, deactivating, reactivating, deleting, assigning, unassigning, denying, and undenying permissions for models.

### Usage

```bash
php artisan gatekeeper:permission
```

### Available Actions

| Action                | Description                                           |
|-----------------------|-------------------------------------------------------|
| Create                | Create one or more new permissions                    |
| Update Name           | Rename an existing permission                         |
| Grant by Default      | Grant one or more permissions by default              |
| Revoke Default Grant  | Revoke the default grant for one or more permissions  |
| Deactivate            | Deactivate one or more active permissions             |
| Reactivate            | Reactivate one or more inactive permissions           |
| Delete                | Permanently delete one or more permissions            |
| Assign                | Assign one or more permissions to a model             |
| Unassign              | Unassign one or more permissions from a model         |
| Deny                  | Deny one or more permissions from a model             |
| Undeny                | Undeny one or more permissions from a model           |

<a name="gatekeeperrole"></a>
## `gatekeeper:role`

Interactively manage Gatekeeper roles via the CLI.

This command supports creating, updating names, granting by default, revoking default grants, deactivating, reactivating, deleting, assigning, unassigning, denying, and undenying roles for models.

### Usage

```bash
php artisan gatekeeper:role
```

### Available Actions

| Action                | Description                                           |
|-----------------------|-------------------------------------------------------|
| Create                | Create one or more new roles                          |
| Update Name           | Rename an existing role                               |
| Grant by Default      | Grant one or more roles by default                    |
| Revoke Default Grant  | Revoke the default grant for one or more roles        |
| Deactivate            | Deactivate one or more active roles                   |
| Reactivate            | Reactivate one or more inactive roles                 |
| Delete                | Permanently delete one or more roles                  |
| Assign                | Assign one or more roles to a model                   |
| Unassign              | Unassign one or more roles from a model               |
| Deny                  | Deny one or more roles from a model                   |
| Undeny                | Undeny one or more roles from a model                 |

<a name="gatekeeperfeature"></a>
## `gatekeeper:feature`

Interactively manage Gatekeeper features via the CLI.

This command supports creating, updating names, granting by default, revoking default grants, deactivating, reactivating, deleting, assigning, unassigning, denying, and undenying features for models.

### Usage

```bash
php artisan gatekeeper:feature
```

### Available Actions

| Action                | Description                                           |
|-----------------------|-------------------------------------------------------|
| Create                | Create one or more new features                       |
| Update Name           | Rename an existing feature                            |
| Grant by Default      | Grant one or more features by default                 |
| Revoke Default Grant  | Revoke the default grant for one or more features     |
| Deactivate            | Deactivate one or more active features                |
| Reactivate            | Reactivate one or more inactive features              |
| Delete                | Permanently delete one or more features               |
| Assign                | Assign one or more features to a model                |
| Unassign              | Unassign one or more features from a model            |
| Deny                  | Deny one or more features from a model                |
| Undeny                | Undeny one or more features from a model              |

<a name="gatekeeperteam"></a>
## `gatekeeper:team`

Interactively manage Gatekeeper teams via the CLI.

This command supports creating, updating names, granting by default, revoking default grants, deactivating, reactivating, deleting, adding to, removing from, denying, and undenying teams for models.

### Usage

```bash
php artisan gatekeeper:team
```

### Available Actions

| Action                | Description                                           |
|-----------------------|-------------------------------------------------------|
| Create                | Create one or more new teams                          |
| Update Name           | Rename an existing team                               |
| Grant by Default      | Grant one or more teams by default                    |
| Revoke Default Grant  | Revoke the default grant for one or more teams        |
| Deactivate            | Deactivate one or more active teams                   |
| Reactivate            | Reactivate one or more inactive teams                 |
| Delete                | Permanently delete one or more teams                  |
| Add                   | Add a model to one or more teams                      |
| Remove                | Remove a model from one or more teams                 |
| Deny                  | Deny one or more teams from a model                   |
| Undeny                | Undeny one or more teams from a model                 |

<a name="gatekeeperclear"></a>
## `gatekeeper:clear`

Invalidate all Gatekeeper-related cache entries.

This command increments the internal cache version, effectively expiring all cache keys related to permissions, roles, features, and teams.

### Usage

```bash
php artisan gatekeeper:clear
```

### Behavior

- Increments an internal `cache.version` value used in all Gatekeeper cache keys.
- This forces all future queries to generate fresh results.
- Does not remove items from the cache store directly, just invalidates them.

<a name="next-steps"></a>
## Next Steps

Entities:
- [Permissions](permissions.md)
- [Roles](roles.md)
- [Features](features.md)
- [Teams](teams.md)

Control Access with Entities:
- [Middleware](middleware.md)
- [Blade Directives](blade-directives.md)

Track Entity and Entity Assignment Changes:
- [Audit Logging](audit-logging.md)
