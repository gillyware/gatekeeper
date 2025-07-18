# Artisan Commands

- [Overview](#overview)
- [gatekeeper:list](#gatekeeperlist)
- [gatekeeper:permission](#gatekeeperpermission)
- [gatekeeper:role](#gatekeeperrole)
- [gatekeeper:team](#gatekeeperteam)
- [gatekeeper:clear-cache](#gatekeeperclear-cache)

<a name="overview"></a>
## Overview

Gatekeeper provides several Artisan commands to help manage permissions, roles, teams, and assignments through the command line. These commands offer an interactive interface for common tasks such as creating, assigning, and revoking entities.

<a name="gatekeeperlist"></a>
## `gatekeeper:list`

List all configured permissions, roles, and teams in your application.

This command displays the name, active status, and timestamps for each permission, role, or team, using your configured timezone.

### Usage

```bash
php artisan gatekeeper:list
```

By default, it will display all permissions, roles, and teams unless filtered with an option.

### Options

| Option          | Description           |
|-------------------------|---------------|
| `--permissions` | Only show permissions |
| `--roles`       | Only show roles       |
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

This command supports creating, updating, deactivating, reactivating, deleting, assigning, and revoking permissions for models.

### Usage

```bash
php artisan gatekeeper:permission
```

### Available Actions

| Action      | Description                                     |
|-------------|-------------------------------------------------|
| Create      | Create one or more new permissions              |
| Update      | Rename an existing permission                   |
| Deactivate  | Mark one or more active permissions as inactive |
| Reactivate  | Restore one or more inactive permissions        |
| Delete      | Permanently delete one or more permissions      |
| Assign      | Assign one or more permissions to a model       |
| Revoke      | Revoke one or more permissions from a model     |

<a name="gatekeeperrole"></a>
## `gatekeeper:role`

Interactively manage Gatekeeper roles via the CLI.

This command supports creating, updating, deactivating, reactivating, deleting, assigning, and revoking roles for models.

### Usage

```bash
php artisan gatekeeper:role
```

### Available Actions

| Action      | Description                                     |
|-------------|-------------------------------------------------|
| Create      | Create one or more new roles                    |
| Update      | Rename an existing role                         |
| Deactivate  | Mark one or more active role as inactive        |
| Reactivate  | Restore one or more inactive roles              |
| Delete      | Permanently delete one or more roles            |
| Assign      | Assign one or more roles to a model             |
| Revoke      | Revoke one or more roles from a model           |

<a name="gatekeeperteam"></a>
## `gatekeeper:team`

Interactively manage Gatekeeper teams via the CLI.

This command supports creating, updating, deactivating, reactivating, deleting, adding models to, and removing models from teams.

### Usage

```bash
php artisan gatekeeper:team
```

### Available Actions

| Action        | Description                               |
|---------------|-------------------------------------------|
| Create        | Create one or more new teams              |
| Update        | Rename an existing team                   |
| Deactivate    | Mark one or more active team as inactive  |
| Reactivate    | Restore one or more inactive teams        |
| Delete        | Permanently delete one or more teams      |
| Add Model To  | Add a model to one or more teams          |
| Revoke        | Remove a model from one or more teams     |

<a name="gatekeeperclear-cache"></a>
## `gatekeeper:clear-cache`

Invalidate all Gatekeeper-related cache entries.

This command increments the internal cache version, effectively expiring all cache keys related to roles, permissions, and teams.

### Usage

```bash
php artisan gatekeeper:clear
```

### Behavior

- Increments an internal `cache.version` value used in all Gatekeeper cache keys.
- This forces all future queries to generate fresh results.
- Does not remove items from the cache store directly—just invalidates them.
