# Permissions

- [Permission Entities](#permission-entities)
    - [Check Permission Existence](#check-permission-existence)
    - [Create Permission](#create-permission)
    - [Update Permission](#update-permission)
    - [Deactivate Permission](#deactivate-permission)
    - [Reactivate Permission](#reactivate-permission)
    - [Delete Permission](#delete-permission)
    - [Find Permission by Name](#find-permission-by-name)
    - [Get All Permissions](#get-all-permissions)
- [Model Permission Assignments](#model-permission-assignments)
    - [Assign Permission to Model](#assign-permission-to-model)
    - [Assign Multiple Permissions to Model](#assign-multiple-permissions-to-model)
    - [Revoke Permission from Model](#revoke-permission-from-model)
    - [Revoke Multiple Permissions from Model](#revoke-multiple-permissions-from-model)
    - [Check Model Has Permission](#check-model-has-permission)
    - [Check Model Has Any Permission](#check-model-has-any-permission)
    - [Check Model Has All Permissions](#check-model-has-all-permissions)
    - [Get Direct Permissions for Model](#get-direct-permissions-for-model)
    - [Get Effective Permissions for Model](#get-effective-permissions-for-model)
    - [Get Verbose Permissions for Model](#get-verbose-permissions-for-model)
- [Next Steps](#next-steps)

A permission is the smallest grantable entity. You can assign permissions directly to any (configured) model, any role, and any team. A model’s effective permissions are the union of its direct permissions and those inherited through its roles, its teams, and the roles attached to those teams.

Gatekeeper exposes a variety of permission-related methods through its facade and `HasPermissions` trait. This section documents each of them with accompanying code examples.

<a name="permission-entities"></a>
## Permission Entities

The following methods deal with managing permission entities themselves.

<a name="check-permission-existence"></a>
### Check Permission Existence

You may check whether a permission exists in the database, regardless of its active or inactive status.

The `permissionExists` method accepts a string or a string-backed enum.

**Returns:** `bool`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$exists = Gatekeeper::permissionExists('users.create');

// or using an enum...

enum Permission: string {
    case CreateUsers = 'users.create';
}

$exists = Gatekeeper::permissionExists(Permission::CreateUsers);
```

<a name="create-permission"></a>
### Create Permission

You may create a new permission, which will be active by default.

If the permission already exists, a `PermissionAlreadyExistsException` will be thrown.

The `createPermission` method accepts a string or a string-backed enum.

**Returns:** `\Gillyware\Gatekeeper\Models\Permission`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$permission = Gatekeeper::createPermission('users.update');

// or using an enum...

enum Permission: string {
    case UpdateUsers = 'users.update';
}

$permission = Gatekeeper::createPermission(Permission::UpdateUsers);
```

<a name="update-permission"></a>
### Update Permission

You may update the name of an existing permission.

The `updatePermission` method accepts a `Permission` model instance, a string, or a string-backed enum as the first argument (the existing permission), and a string or string-backed enum as the second argument (the new name).

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

If a permission with the new name already exists, a `PermissionAlreadyExistsException` will be thrown.

**Returns:** `\Gillyware\Gatekeeper\Models\Permission`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$updatedPermission = Gatekeeper::updatePermission('users.update', 'users.modify');

// or using enums...

enum Permission: string {
    case UpdateUsers = 'users.update';
    case ModifyUsers = 'users.modify';
}

$updatedPermission = Gatekeeper::updatePermission(Permission::UpdateUsers, Permission::ModifyUsers);
```

<a name="deactivate-permission"></a>
### Deactivate Permission

You may temporarily deactivate a permission if you want it to stop granting access without revoking it from models.

Deactivated permissions remain in the database but are ignored by permission checks until reactivated.

The `deactivatePermission` method accepts a `Permission` model instance, a string, or a string-backed enum.

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

If the permission is already inactive, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Models\Permission`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$deactivatedPermission = Gatekeeper::deactivatePermission('users.delete');

// or using an enum...

enum Permission: string {
    case DeleteUsers = 'users.delete';
}

$deactivatedPermission = Gatekeeper::deactivatePermission(Permission::DeleteUsers);
```

<a name="reactivate-permission"></a>
### Reactivate Permission

You may reactivate an inactive permission to resume granting access to models.

The `reactivatePermission` method accepts a `Permission` model instance, a string, or a string-backed enum.

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

If the permission is already active, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Models\Permission`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$reactivatedPermission = Gatekeeper::reactivatePermission('users.delete');

// or using an enum...

enum Permission: string {
    case DeleteUsers = 'users.delete';
}

$reactivatedPermission = Gatekeeper::reactivatePermission(Permission::DeleteUsers);
```

<a name="delete-permission"></a>
### Delete Permission

You may delete a permission to remove it from your application.

> [!WARNING]
> Deleting a permission will remove it from your application and unassign it from all models.

The `deletePermission` method accepts a `Permission` model instance, a string, or a string-backed enum.

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

If the permission is already deleted, the method will return `true` without raising an exception.

**Returns:** `bool`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$permissionDeleted = Gatekeeper::deletePermission('projects.create');

// or using an enum...

enum Permission: string {
    case CreateProjects = 'projects.create';
}

$permissionDeleted = Gatekeeper::deletePermission(Permission::CreateProjects);
```

<a name="find-permission-by-name"></a>
### Find Permission by Name

You may retrieve a permission by its name. If the permission does not exist, `null` will be returned.

The `findPermissionByName` method accepts a string or a string-backed enum.

**Returns:** `\Gillyware\Gatekeeper\Models\Permission|null`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$permission = Gatekeeper::findPermissionByName('projects.update');

// or using an enum...

enum Permission: string {
    case UpdateProjects = 'projects.update';
}

$permission = Gatekeeper::findPermissionByName(Permission::UpdateProjects);
```

<a name="get-all-permissions"></a>
### Get All Permissions

You may retrieve a collection of all permissions defined in your application, regardless of their active status.

The `getAllPermissions` method does not take any arguments.

**Returns:** `\Illuminate\Support\Collection<\Gillyware\Gatekeeper\Models\Permission>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$permissions = Gatekeeper::getAllPermissions();
```

<a name="model-permission-assignments"></a>
## Model Permission Assignments

The following methods allow you to assign, revoke, and inspect permissions for models.

> [!NOTE]
> Models passed to Gatekeeper must use the `\Gillyware\Gatekeeper\Traits\HasPermissions` trait for the following functionality.

<a name="assign-permission-to-model"></a>
### Assign Permission to Model

You may assign a permission to a model using one of the following approaches:

- Using the static `Gatekeeper::assignPermissionToModel($model, $permission)` method
- Using the fluent `Gatekeeper::for($model)->assignPermission($permission)` chain
- Calling `$model->assignPermission($permission)` directly (available via the `HasPermissions` trait)

The `$permission` argument can be a:

- `Permission` model instance
- string (e.g. `'users.view'`)
- string-backed enum value

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

**Returns:** `bool` – `true` if the permission was newly assigned or already present

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case DeleteProjects = 'projects.delete';
}

$user = User::query()->findOrFail(1);

$permissionAssigned = Gatekeeper::assignPermissionToModel($user, Permission::DeleteProjects);

// or fluently...

$permissionAssigned = Gatekeeper::for($user)->assignPermission(Permission::DeleteProjects);

// or via the trait method...

$permissionAssigned = $user->assignPermission(Permission::DeleteProjects);
```

<a name="assign-multiple-permissions-to-model"></a>
### Assign Multiple Permissions to Model

You may assign multiple permissions to a model using one of the following approaches:

- Using the static `Gatekeeper::assignAllPermissionsToModel($model, $permissions)` method
- Using the fluent `Gatekeeper::for($model)->assignAllPermissions($permissions)` chain
- Calling `$model->assignAllPermissions($permissions)` directly (available via the `HasPermissions` trait)

The `$permissions` argument must be an array or Arrayable containing any combination of:

- `Permission` model instance
- string (e.g. `'users.view'`)
- string-backed enum value

If a permission is already assigned, it will be skipped without raising an exception.

If a permission does not exist, a `PermissionNotFoundException` will be thrown.

> [!NOTE]
> This method stops on the first failure.

**Returns:** `bool` – `true` if all permissions were successfully assigned or already present

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case ViewProjects = 'projects.view';
    case CreateProjects = 'projects.create';
    case UpdateProjects = 'projects.update';
}

$permissions = [Permission::ViewProjects, Permission::CreateProjects, Permission::UpdateProjects];

$user = User::query()->findOrFail(1);

$permissionsAssigned = Gatekeeper::assignAllPermissionsToModel($user, $permissions);

// or fluently...

$permissionsAssigned = Gatekeeper::for($user)->assignAllPermissions($permissions);

// or via the trait method...

$permissionsAssigned = $user->assignAllPermissions($permissions);
```

<a name="revoke-permission-from-model"></a>
### Revoke Permission from Model

You may revoke a permission from a model using one of the following approaches:

- Using the static `Gatekeeper::revokePermissionFromModel($model, $permission)` method
- Using the fluent `Gatekeeper::for($model)->revokePermission($permission)` chain
- Calling `$model->revokePermission($permission)` directly (available via the `HasPermissions` trait)

The `$permission` argument can be a:

- `Permission` model instance
- string (e.g. `'users.view'`)
- string-backed enum value

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

**Returns:** bool – `true` if the permission was removed or was not previously assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case DeletePosts = 'posts.delete';
}

$user = User::query()->findOrFail(1);

$permissionRevoked = Gatekeeper::revokePermissionFromModel($user, Permission::DeletePosts);

// or fluently...

$permissionRevoked = Gatekeeper::for($user)->revokePermission(Permission::DeletePosts);

// or via the trait method...

$permissionRevoked = $user->revokePermission(Permission::DeletePosts);
```

<a name="revoke-multiple-permissions-from-model"></a>
### Revoke Multiple Permissions from Model

You may revoke multiple permissions from a model using one of the following approaches:

- Using the static `Gatekeeper::revokeAllPermissionsFromModel($model, $permissions)` method
- Using the fluent `Gatekeeper::for($model)->revokeAllPermissions($permissions)` chain
- Calling `$model->revokeAllPermissions($permissions)` directly (available via the `HasPermissions` trait)

The `$permissions` argument must be an array or Arrayable containing any combination of:

- `Permission` model instance
- string (e.g. `'users.view'`)
- string-backed enum value

If a permission is already unassigned, it will be skipped without raising an exception.

If a permission does not exist, a `PermissionNotFoundException` will be thrown.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if all permissions were revoked or were not previously assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case ViewProjects = 'projects.view';
    case CreateProjects = 'projects.create';
    case UpdateProjects = 'projects.update';
}

$permissions = [Permission::ViewProjects, Permission::CreateProjects, Permission::UpdateProjects];

$user = User::query()->findOrFail(1);

$permissionsRevoked = Gatekeeper::revokeAllPermissionsFromModel($user, $permissions);

// or fluently...

$permissionsRevoked = Gatekeeper::for($user)->revokeAllPermissions($permissions);

// or via the trait method...

$permissionsRevoked = $user->revokeAllPermissions($permissions);
```

<a name="check-model-has-permission"></a>
### Check Model Has Permission

A model may have an active permission directly assigned or indirectly assigned through a role, team, or team's role. This method checks all sources to determine if the model has access to the permission.

You may check if a model has a permission using one of the following approaches:

- Using the static `Gatekeeper::modelHasPermission($model, $permission)` method
- Using the fluent `Gatekeeper::for($model)->hasPermission($permission)` chain
- Calling `$model->hasPermission($permission)` directly (available via the `HasPermissions` trait)

The `$permission` argument can be a:

- `Permission` model instance
- string (e.g. `'users.view'`)
- string-backed enum value

If the permission does not exist, `false` will be returned.

**Returns:** bool – `true` if the model has access to the given permission, `false` otherwise (including if the model is missing the `HasPermissions` trait, the permission does not exist, or the permission is inactive)

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case DeletePosts = 'posts.delete';
}

$user = User::query()->findOrFail(1);

$hasPermission = Gatekeeper::modelHasPermission($user, Permission::DeletePosts);

// or fluently...

$hasPermission = Gatekeeper::for($user)->hasPermission(Permission::DeletePosts);

// or via the trait method...

$hasPermission = $user->hasPermission(Permission::DeletePosts);
```

<a name="check-model-has-any-permission"></a>
### Check Model Has Any Permission

A model may have an active permission directly assigned or indirectly assigned through a role, team, or team's role. This method checks all sources to determine if the model has access to any of the given permissions.

You may check if a model has any of a set of permissions using one of the following approaches:

- Using the static `Gatekeeper::modelHasAnyPermission($model, $permissions)` method
- Using the fluent `Gatekeeper::for($model)->hasAnyPermission($permissions)` chain
- Calling `$model->hasAnyPermission($permissions)` directly (available via the `HasPermissions` trait)

The `$permissions` argument must be an array or Arrayable containing any combination of:

- `Permission` model instance
- string (e.g. `'users.view'`)
- string-backed enum value

If the permission does not exist, it will be skipped.

**Returns:** bool – `true` if the model has access to any of the given permissions, `false` otherwise (including if none of the permissions exist, are active, or are assigned)

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case ViewProjects = 'projects.view';
    case CreateProjects = 'projects.create';
    case UpdateProjects = 'projects.update';
}

$permissions = [Permission::ViewProjects, Permission::CreateProjects, Permission::UpdateProjects];

$user = User::query()->findOrFail(1);

$hasAnyPermission = Gatekeeper::modelHasAnyPermission($user, $permissions);

// or fluently...

$hasAnyPermission = Gatekeeper::for($user)->hasAnyPermission($permissions);

// or via the trait method...

$hasAnyPermission = $user->hasAnyPermission($permissions);
```

<a name="check-model-has-all-permissions"></a>
### Check Model Has All Permissions

A model may have an active permission directly assigned or indirectly assigned through a role, team, or team's role. This method checks all sources to determine if the model has access to all of the given permissions.

You may check if a model has all of a set of permissions using one of the following approaches:

- Using the static `Gatekeeper::modelHasAllPermissions($model, $permissions)` method
- Using the fluent `Gatekeeper::for($model)->hasAllPermissions($permissions)` chain
- Calling `$model->hasAllPermissions($permissions)` directly (available via the `HasPermissions` trait)

The `$permissions` argument must be an array or Arrayable containing any combination of:

- `Permission` model instance
- string (e.g. `'users.view'`)
- string-backed enum value

If the permission does not exist, `false` will be returned.

**Returns:** bool – `true` if the model has access to all of the given permissions, `false` otherwise (including if any of the permissions do not exist, are inactive, or are unassigned)

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case ViewProjects = 'projects.view';
    case CreateProjects = 'projects.create';
    case UpdateProjects = 'projects.update';
}

$permissions = [Permission::ViewProjects, Permission::CreateProjects, Permission::UpdateProjects];

$user = User::query()->findOrFail(1);

$hasAllPermissions = Gatekeeper::modelHasAllPermissions($user, $permissions);

// or fluently...

$hasAllPermissions = Gatekeeper::for($user)->hasAllPermissions($permissions);

// or via the trait method...

$hasAllPermissions = $user->hasAllPermissions($permissions);
```

<a name="get-direct-permissions-for-model"></a>
### Get Direct Permissions for Model

You may retrieve a collection of all permissions directly assigned to a given model, regardless of their active status. This does not include permissions inherited from roles or teams.

You may get a model's direct permissions using one of the following approaches:

- Using the static `Gatekeeper::getDirectPermissionsForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getDirectPermissions()` chain
- Calling `$model->getDirectPermissions()` directly (available via the `HasPermissions` trait)

**Returns:** `\Illuminate\Support\Collection<\Gillyware\Gatekeeper\Models\Permission>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$directPermissions = Gatekeeper::getDirectPermissionsForModel($user);

// or fluently...

$directPermissions = Gatekeeper::for($user)->getDirectPermissions();

// or via the trait method...

$directPermissions = $user->getDirectPermissions();
```

<a name="get-effective-permissions-for-model"></a>
### Get Effective Permissions for Model

You may retrieve a collection of all active permissions effectively assigned to a given model, including those inherited from roles and teams.

You may get a model's effective permissions using one of the following approaches:

- Using the static `Gatekeeper::getEffectivePermissionsForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getEffectivePermissions()` chain
- Calling `$model->getEffectivePermissions()` directly (available via the `HasPermissions` trait)

**Returns:** `\Illuminate\Support\Collection<\Gillyware\Gatekeeper\Models\Permission>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$effectivePermissions = Gatekeeper::getEffectivePermissionsForModel($user);

// or fluently...

$effectivePermissions = Gatekeeper::for($user)->getEffectivePermissions();

// or via the trait method...

$effectivePermissions = $user->getEffectivePermissions();
```

<a name="get-verbose-permissions-for-model"></a>
### Get Verbose Permissions for Model

You may retrieve a collection of all active permissions effectively assigned to a given model, along with the source(s) of each permission (e.g., direct, via role, via team).

You may get a model's verbose permissions using one of the following approaches:

- Using the static `Gatekeeper::getVerbosePermissionsForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getVerbosePermissions()` chain
- Calling `$model->getVerbosePermissions()` directly (available via the `HasPermissions` trait)

**Returns:** `\Illuminate\Support\Collection<array{name: string, sources: array}>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$verbosePermissions = Gatekeeper::getVerbosePermissionsForModel($user);

// or fluently...

$verbosePermissions = Gatekeeper::for($user)->getVerbosePermissions();

// or via the trait method...

$verbosePermissions = $user->getVerbosePermissions();
```

<a name="next-steps"></a>
## Next Steps

Now that you've learned how to manage permissions, you may explore how to group them using roles:

[Roles](roles.md)
