# Permissions

- [Permission Entities](#permission-entities)
    - [Check Permission Existence](#check-permission-existence)
    - [Create Permission](#create-permission)
    - [Update Permission Name](#update-permission-name)
    - [Grant Permission by Default](#grant-permission-by-default)
    - [Revoke Permission Default Grant](#revoke-permission-default-grant)
    - [Deactivate Permission](#deactivate-permission)
    - [Reactivate Permission](#reactivate-permission)
    - [Delete Permission](#delete-permission)
    - [Find Permission by Name](#find-permission-by-name)
    - [Get All Permissions](#get-all-permissions)
- [Model Permission Relationships](#model-permission-relationships)
    - [Assign Permission to Model](#assign-permission-to-model)
    - [Assign Multiple Permissions to Model](#assign-multiple-permissions-to-model)
    - [Unassign Permission from Model](#unassign-permission-from-model)
    - [Unassign Multiple Permissions from Model](#unassign-multiple-permissions-from-model)
    - [Deny Permission from Model](#deny-permission-from-model)
    - [Deny Multiple Permissions from Model](#deny-multiple-permissions-from-model)
    - [Undeny Permission from Model](#undeny-permission-from-model)
    - [Undeny Multiple Permissions from Model](#undeny-multiple-permissions-from-model)
    - [Check Model Has Permission](#check-model-has-permission)
    - [Check Model Has Any Permission](#check-model-has-any-permission)
    - [Check Model Has All Permissions](#check-model-has-all-permissions)
    - [Get Direct Permissions for Model](#get-direct-permissions-for-model)
    - [Get Effective Permissions for Model](#get-effective-permissions-for-model)
    - [Get Verbose Permissions for Model](#get-verbose-permissions-for-model)
- [Next Steps](#next-steps)

A permission is the smallest grantable entity. You can assign permissions directly to any (configured) model, any role, any feature, and any team.

By default, created permissions are active and not granted by default. 'Active' means the permission is actively granting access, and 'not granted by default' means a permission must be explicitly assigned to models, either directly or through another entity directly assigned to the model.

A model’s effective permissions are the union of its permissions granted by default, direct permissions, and those inherited through its roles, its teams, its features and the roles attached to its teams, excluding the permissions directly denied from the model. Keep in mind, a model will effectively have no permissions (granted by default, direct, or inherited) if the model is not using the `HasPermissions` trait.

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

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$permission = Gatekeeper::createPermission('users.update');

// or using an enum...

enum Permission: string {
    case UpdateUsers = 'users.update';
}

$permission = Gatekeeper::createPermission(Permission::UpdateUsers);
```

<a name="update-permission-name"></a>
### Update Permission Name

You may update the name of an existing permission.

The `updatePermissionName` method accepts a `PermissionPacket` instance, a string, or a string-backed enum as the first argument (the existing permission), and a string or string-backed enum as the second argument (the new name).

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

If a permission with the new name already exists, a `PermissionAlreadyExistsException` will be thrown.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$updatedPermission = Gatekeeper::updatePermissionName('users.update', 'users.modify');

// or using enums...

enum Permission: string {
    case UpdateUsers = 'users.update';
    case ModifyUsers = 'users.modify';
}

$updatedPermission = Gatekeeper::updatePermissionName(Permission::UpdateUsers, Permission::ModifyUsers);
```

<a name="grant-permission-by-default"></a>
### Grant Permission by Default

You may want a permission that most, if not all, models should have by default. Granting the permission by default effectively assigns it to all models that are not denying it.

The `grantPermissionByDefault` method accepts a `PermissionPacket` instance, a string, or a string-backed enum.

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

If the permission is already granted by default, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$grantedByDefaultPermission = Gatekeeper::grantPermissionByDefault('users.delete');

// or using an enum...

enum Permission: string {
    case DeleteUsers = 'users.delete';
}

$grantedByDefaultPermission = Gatekeeper::grantPermissionByDefault(Permission::DeleteUsers);
```

<a name="revoke-permission-default-grant"></a>
### Revoke Permission Default Grant

You may decide that a permission should not be [granted by default](#grant-permission-by-default). You can easily revoke a permission's default grant.

The `revokePermissionDefaultGrant` method accepts a `PermissionPacket` instance, a string, or a string-backed enum.

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

If the permission is not granted by default, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$nonGrantedByDefaultPermission = Gatekeeper::revokePermissionDefaultGrant('users.delete');

// or using an enum...

enum Permission: string {
    case DeleteUsers = 'users.delete';
}

$nonGrantedByDefaultPermission = Gatekeeper::revokePermissionDefaultGrant(Permission::DeleteUsers);
```

<a name="deactivate-permission"></a>
### Deactivate Permission

You may temporarily deactivate a permission if you want it to stop granting access without unassigning it from models.

Deactivated permissions remain in the database but are ignored by permission checks until reactivated.

The `deactivatePermission` method accepts a `PermissionPacket` instance, a string, or a string-backed enum.

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

If the permission is already inactive, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket`

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

The `reactivatePermission` method accepts a `PermissionPacket` instance, a string, or a string-backed enum.

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

If the permission is already active, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket`

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

The `deletePermission` method accepts a `PermissionPacket` instance, a string, or a string-backed enum.

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

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket|null`

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

**Returns:** `\Illuminate\Support\Collection<string, \Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$permissions = Gatekeeper::getAllPermissions();
```

<a name="model-permission-relationships"></a>
## Model Permission Relationships

The following methods allow you to assign, unassign, deny, undeny, and inspect permissions for models.

> [!NOTE]
> Models passed to Gatekeeper must use the `\Gillyware\Gatekeeper\Traits\HasPermissions` trait for the following functionality.

<a name="assign-permission-to-model"></a>
### Assign Permission to Model

You may assign a permission to a model using one of the following approaches:

- Using the static `Gatekeeper::assignPermissionToModel($model, $permission)` method
- Using the fluent `Gatekeeper::for($model)->assignPermission($permission)` chain
- Calling `$model->assignPermission($permission)` directly (available via the `HasPermissions` trait)

The `$permission` argument must be a `PermissionPacket` instance, a string, or a string-backed enum.

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

If the permission is denied from a model, the denial will be removed before assigning.

**Returns:** `bool` – `true` if the permission is assigned

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

The `$permissions` argument must be an array or Arrayable containing any combination `PermissionPacket` instances, strings, or a string-backed enums.

If a permission is already assigned, it will be skipped without raising an exception.

If a permission does not exist, a `PermissionNotFoundException` will be thrown.

If a permission is denied from a model, the denial will be removed before assigning.

> [!NOTE]
> This method stops on the first failure.

**Returns:** `bool` – `true` if all permissions are assigned

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

<a name="unassign-permission-from-model"></a>
### Unassign Permission from Model

You may unassign a permission from a model using one of the following approaches:

- Using the static `Gatekeeper::unassignPermissionFromModel($model, $permission)` method
- Using the fluent `Gatekeeper::for($model)->unassignPermission($permission)` chain
- Calling `$model->unassignPermission($permission)` directly (available via the `HasPermissions` trait)

The `$permission` argument must be a `PermissionPacket` instance, a string, or a string-backed enum.

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

If the permission is denied from the model, the denial will remain intact.

**Returns:** bool – `true` if the permission is not assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case DeletePosts = 'posts.delete';
}

$user = User::query()->findOrFail(1);

$permissionUnassigned = Gatekeeper::unassignPermissionFromModel($user, Permission::DeletePosts);

// or fluently...

$permissionUnassigned = Gatekeeper::for($user)->unassignPermission(Permission::DeletePosts);

// or via the trait method...

$permissionUnassigned = $user->unassignPermission(Permission::DeletePosts);
```

<a name="unassign-multiple-permissions-from-model"></a>
### Unassign Multiple Permissions from Model

You may unassign multiple permissions from a model using one of the following approaches:

- Using the static `Gatekeeper::unassignAllPermissionsFromModel($model, $permissions)` method
- Using the fluent `Gatekeeper::for($model)->unassignAllPermissions($permissions)` chain
- Calling `$model->unassignAllPermissions($permissions)` directly (available via the `HasPermissions` trait)

The `$permissions` argument must be an array or Arrayable containing any combination `PermissionPacket` instances, strings, or a string-backed enums.

If a permission is already unassigned, it will be skipped without raising an exception.

If a permission does not exist, a `PermissionNotFoundException` will be thrown.

If the permission is denied from the model, the denial will remain intact.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if none of the permissions are assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case ViewProjects = 'projects.view';
    case CreateProjects = 'projects.create';
    case UpdateProjects = 'projects.update';
}

$permissions = [Permission::ViewProjects, Permission::CreateProjects, Permission::UpdateProjects];

$user = User::query()->findOrFail(1);

$permissionsUnassigned = Gatekeeper::unassignAllPermissionsFromModel($user, $permissions);

// or fluently...

$permissionsUnassigned = Gatekeeper::for($user)->unassignAllPermissions($permissions);

// or via the trait method...

$permissionsUnassigned = $user->unassignAllPermissions($permissions);
```

<a name="deny-permission-from-model"></a>
### Deny Permission from Model

To deny a permission from a model means to block access to a permission even if the permission is granted by default or inherited from another entity.

You may deny a permission from a model using one of the following approaches:

- Using the static `Gatekeeper::denyPermissionFromModel($model, $permission)` method
- Using the fluent `Gatekeeper::for($model)->denyPermission($permission)` chain
- Calling `$model->denyPermission($permission)` directly (available via the `HasPermissions` trait)

The `$permission` argument must be a `PermissionPacket` instance, a string, or a string-backed enum.

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

If the permission is assigned to the model, the permission will be unassigned from the model before denying.

**Returns:** bool – `true` if the permission is denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case DeletePosts = 'posts.delete';
}

$user = User::query()->findOrFail(1);

$permissionDenied = Gatekeeper::denyPermissionFromModel($user, Permission::DeletePosts);

// or fluently...

$permissionDenied = Gatekeeper::for($user)->denyPermission(Permission::DeletePosts);

// or via the trait method...

$permissionDenied = $user->denyPermission(Permission::DeletePosts);
```

<a name="deny-multiple-permissions-from-model"></a>
### Deny Multiple Permissions from Model

You may deny multiple permissions from a model using one of the following approaches:

- Using the static `Gatekeeper::denyAllPermissionsFromModel($model, $permissions)` method
- Using the fluent `Gatekeeper::for($model)->denyAllPermissions($permissions)` chain
- Calling `$model->denyAllPermissions($permissions)` directly (available via the `HasPermissions` trait)

The `$permissions` argument must be an array or Arrayable containing any combination `PermissionPacket` instances, strings, or a string-backed enums.

If a permission is already denied, it will be skipped without raising an exception.

If a permission does not exist, a `PermissionNotFoundException` will be thrown.

If the permission is denied from the model, the denial will remain intact.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if all permissions are denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case ViewProjects = 'projects.view';
    case CreateProjects = 'projects.create';
    case UpdateProjects = 'projects.update';
}

$permissions = [Permission::ViewProjects, Permission::CreateProjects, Permission::UpdateProjects];

$user = User::query()->findOrFail(1);

$permissionsDenied = Gatekeeper::denyAllPermissionsFromModel($user, $permissions);

// or fluently...

$permissionsDenied = Gatekeeper::for($user)->denyAllPermissions($permissions);

// or via the trait method...

$permissionsDenied = $user->denyAllPermissions($permissions);
```

<a name="undeny-permission-from-model"></a>
### Undeny Permission from Model

To undeny a permission from a model means to unblock access to a permission, allowing acces if the permission is granted by default, directly assigned, or inherited from another entity.

You may undeny a permission from a model using one of the following approaches:

- Using the static `Gatekeeper::undenyPermissionFromModel($model, $permission)` method
- Using the fluent `Gatekeeper::for($model)->undenyPermission($permission)` chain
- Calling `$model->undenyPermission($permission)` directly (available via the `HasPermissions` trait)

The `$permission` argument must be a `PermissionPacket` instance, a string, or a string-backed enum.

If the permission does not exist, a `PermissionNotFoundException` will be thrown.

**Returns:** bool – `true` if the permission is not denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case DeletePosts = 'posts.delete';
}

$user = User::query()->findOrFail(1);

$permissionUndenied = Gatekeeper::undenyPermissionFromModel($user, Permission::DeletePosts);

// or fluently...

$permissionUndenied = Gatekeeper::for($user)->undenyPermission(Permission::DeletePosts);

// or via the trait method...

$permissionUndenied = $user->undenyPermission(Permission::DeletePosts);
```

<a name="undeny-multiple-permissions-from-model"></a>
### Undeny Multiple Permissions from Model

You may undeny multiple permissions from a model using one of the following approaches:

- Using the static `Gatekeeper::undenyAllPermissionsFromModel($model, $permissions)` method
- Using the fluent `Gatekeeper::for($model)->undenyAllPermissions($permissions)` chain
- Calling `$model->undenyAllPermissions($permissions)` directly (available via the `HasPermissions` trait)

The `$permissions` argument must be an array or Arrayable containing any combination `PermissionPacket` instances, strings, or a string-backed enums.

If a permission is not denied, it will be skipped without raising an exception.

If a permission does not exist, a `PermissionNotFoundException` will be thrown.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if none of the permissions are denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Permission: string {
    case ViewProjects = 'projects.view';
    case CreateProjects = 'projects.create';
    case UpdateProjects = 'projects.update';
}

$permissions = [Permission::ViewProjects, Permission::CreateProjects, Permission::UpdateProjects];

$user = User::query()->findOrFail(1);

$permissionsUndenied = Gatekeeper::undenyAllPermissionsFromModel($user, $permissions);

// or fluently...

$permissionsUndenied = Gatekeeper::for($user)->undenyAllPermissions($permissions);

// or via the trait method...

$permissionsUndenied = $user->undenyAllPermissions($permissions);
```

<a name="check-model-has-permission"></a>
### Check Model Has Permission

A model may have an undenied, active permission granted by default, directly assigned, or indirectly assigned through a role, feature, team, or team's role. This method checks all sources to determine if the model has access to the permission.

You may check if a model has a permission using one of the following approaches:

- Using the static `Gatekeeper::modelHasPermission($model, $permission)` method
- Using the fluent `Gatekeeper::for($model)->hasPermission($permission)` chain
- Calling `$model->hasPermission($permission)` directly (available via the `HasPermissions` trait)

The `$permission` argument must be a `PermissionPacket` instance, a string, or a string-backed enum.

If the permission does not exist, `false` will be returned.

**Returns:** bool – `true` if the model has access to the given permission

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

A model may have an undenied, active permission granted by default, directly assigned, or indirectly assigned through a role, feature, team, or team's role. This method checks all sources to determine if the model has access to any of the given permissions.

You may check if a model has any of a set of permissions using one of the following approaches:

- Using the static `Gatekeeper::modelHasAnyPermission($model, $permissions)` method
- Using the fluent `Gatekeeper::for($model)->hasAnyPermission($permissions)` chain
- Calling `$model->hasAnyPermission($permissions)` directly (available via the `HasPermissions` trait)

The `$permissions` argument must be an array or Arrayable containing any combination `PermissionPacket` instances, strings, or a string-backed enums.

If the permission does not exist, it will be skipped.

**Returns:** bool – `true` if the model has access to any of the given permissions

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

A model may have an undenied, active permission granted by default, directly assigned, or indirectly assigned through a role, feature, team, or team's role. This method checks all sources to determine if the model has access to all of the given permissions.

You may check if a model has all of a set of permissions using one of the following approaches:

- Using the static `Gatekeeper::modelHasAllPermissions($model, $permissions)` method
- Using the fluent `Gatekeeper::for($model)->hasAllPermissions($permissions)` chain
- Calling `$model->hasAllPermissions($permissions)` directly (available via the `HasPermissions` trait)

The `$permissions` argument must be an array or Arrayable containing any combination `PermissionPacket` instances, strings, or a string-backed enums.

If the permission does not exist, `false` will be returned.

**Returns:** bool – `true` if the model has access to all of the given permissions

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

You may retrieve a collection of all permissions directly assigned to a given model, regardless of their active status. This does not include permissions inherited from roles, features, or teams.

You may get a model's direct permissions using one of the following approaches:

- Using the static `Gatekeeper::getDirectPermissionsForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getDirectPermissions()` chain
- Calling `$model->getDirectPermissions()` directly (available via the `HasPermissions` trait)

**Returns:** `\Illuminate\Support\Collection<string, \Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket>`

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

You may retrieve a collection of all undenied, active permissions effectively assigned to a given model, including those granted by default and inherited from roles and teams.

You may get a model's effective permissions using one of the following approaches:

- Using the static `Gatekeeper::getEffectivePermissionsForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getEffectivePermissions()` chain
- Calling `$model->getEffectivePermissions()` directly (available via the `HasPermissions` trait)

**Returns:** `\Illuminate\Support\Collection<string, \Gillyware\Gatekeeper\Packets\Entities\Permission\PermissionPacket>`

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

You may retrieve a collection of all undenied, active permissions effectively assigned to a given model, along with the source(s) of each permission.

You may get a model's verbose permissions using one of the following approaches:

- Using the static `Gatekeeper::getVerbosePermissionsForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getVerbosePermissions()` chain
- Calling `$model->getVerbosePermissions()` directly (available via the `HasPermissions` trait)

**Returns:** `\Illuminate\Support\Collection`

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

Entities:
- [Roles](roles.md)
- [Features](features.md)
- [Teams](teams.md)

Control Access with Entities:
- [Middleware](middleware.md)
- [Blade Directives](blade-directives.md)

Manage Entities and Assignments:
- [Artisan Commands](artisan-commands.md)

Track Entity and Entity Assignment Changes:
- [Audit Logging](audit-logging.md)
