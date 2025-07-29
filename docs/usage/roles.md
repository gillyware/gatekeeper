# Roles

- [Role Entities](#role-entities)
    - [Check Role Existence](#check-role-existence)
    - [Create Role](#create-role)
    - [Update Role Name](#update-role-name)
    - [Grant Role by Default](#grant-role-by-default)
    - [Revoke Role Default Grant](#revoke-role-default-grant)
    - [Deactivate Role](#deactivate-role)
    - [Reactivate Role](#reactivate-role)
    - [Delete Role](#delete-role)
    - [Find Role by Name](#find-role-by-name)
    - [Get All Roles](#get-all-roles)
- [Model Role Relationships](#model-role-relationships)
    - [Assign Role to Model](#assign-role-to-model)
    - [Assign Multiple Roles to Model](#assign-multiple-roles-to-model)
    - [Unassign Role from Model](#unassign-role-from-model)
    - [Unassign Multiple Roles from Model](#unassign-multiple-roles-from-model)
    - [Deny Role from Model](#deny-role-from-model)
    - [Deny Multiple Roles from Model](#deny-multiple-roles-from-model)
    - [Undeny Role from Model](#undeny-role-from-model)
    - [Undeny Multiple Roles from Model](#undeny-multiple-roles-from-model)
    - [Check Model Has Role](#check-model-has-role)
    - [Check Model Has Any Role](#check-model-has-any-role)
    - [Check Model Has All Roles](#check-model-has-all-roles)
    - [Get Direct Roles for Model](#get-direct-roles-for-model)
    - [Get Effective Roles for Model](#get-effective-roles-for-model)
    - [Get Verbose Roles for Model](#get-verbose-roles-for-model)
- [Next Steps](#next-steps)

A role is a named grouping of permissions. You can assign roles directly to any (configured) model and any team.

By default, created roles are active and not granted by default. 'Active' means the role is actively granting access, and 'not granted by default' means a role must be explicitly assigned to models, either directly or through another entity directly assigned to the model.

A model’s effective roles are the union of its roles granted by default, direct roles, and those inherited through its teams, excluding the roles directly denied from the model. Keep in mind, a model will effectively have no roles (granted by default, direct, or inherited) if the model is not using the `HasRoles` trait.

Gatekeeper exposes a variety of role-related methods through its facade and `HasRoles` trait. This section documents each of them with accompanying code examples.

<a name="role-entities"></a>
## Role Entities

The following methods deal with managing role entities themselves.

<a name="check-role-existence"></a>
### Check Role Existence

You may check whether a role exists in the database, regardless of its active or inactive status.

The `roleExists` method accepts a string or a string-backed enum.

**Returns:** `bool`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$exists = Gatekeeper::roleExists('user_manager');

// or using an enum...

enum Role: string {
    case UserManager = 'user_manager';
}

$exists = Gatekeeper::roleExists(Role::UserManager);
```

<a name="create-role"></a>
### Create Role

You may create a new role, which will be active by default.

If the role already exists, a `RoleAlreadyExistsException` will be thrown.

The `createRole` method accepts a string or a string-backed enum.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$role = Gatekeeper::createRole('product_manager');

// or using an enum...

enum Role: string {
    case ProductManager = 'product_manager';
}

$role = Gatekeeper::createRole(Role::ProductManager);
```

<a name="update-role-name"></a>
### Update Role Name

You may update the name of an existing role.

The `updateRoleName` method accepts a `RolePacket` instance, a string, or a string-backed enum as the first argument (the existing role), and a string or string-backed enum as the second argument (the new name).

If the role does not exist, a `RoleNotFoundException` will be thrown.

If a role with the new name already exists, a `RoleAlreadyExistsException` will be thrown.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$updatedRole = Gatekeeper::updateRoleName('product_manager', 'product_owner');

// or using enums...

enum Role: string {
    case ProductManager = 'product_manager';
    case ProductOwner = 'product_owner';
}

$updatedRole = Gatekeeper::updateRoleName(Role::ProductManager, Role::ProductOwner);
```

<a name="grant-role-by-default"></a>
### Grant Role by Default

You may want a role that most, if not all, models should have by default. Granting the role by default effectively assigns it to all models that are not denying it.

The `grantRoleByDefault` method accepts a `RolePacket` instance, a string, or a string-backed enum.

If the role does not exist, a `RoleNotFoundException` will be thrown.

If the role is already granted by default, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$grantedByDefaultRole = Gatekeeper::grantRoleByDefault('product_manager');

// or using an enum...

enum Role: string {
    case ProductManager = 'product_manager';
}

$grantedByDefaultRole = Gatekeeper::grantRoleByDefault(Role::DeleteUsers);
```

<a name="revoke-role-default-grant"></a>
### Revoke Role Default Grant

You may decide that a role should not be [granted by default](#grant-role-by-default). You can easily revoke a role's default grant.

The `revokeRoleDefaultGrant` method accepts a `RolePacket` instance, a string, or a string-backed enum.

If the role does not exist, a `RoleNotFoundException` will be thrown.

If the role is not granted by default, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$nonGrantedByDefaultRole = Gatekeeper::revokeRoleDefaultGrant('product_manager');

// or using an enum...

enum Role: string {
    case ProductManager = 'product_manager';
}

$nonGrantedByDefaultRole = Gatekeeper::revokeRoleDefaultGrant(Role::DeleteUsers);
```

<a name="deactivate-role"></a>
### Deactivate Role

You may temporarily deactivate a role if you want it to stop granting access without unassigning it from models.

Deactivated roles remain in the database but are ignored by role checks until reactivated. The permissions attached to roles will also be ignored until the role is reactivated.

The `deactivateRole` method accepts a `RolePacket` instance, a string, or a string-backed enum.

If the role does not exist, a `RoleNotFoundException` will be thrown.

If the role is already inactive, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$deactivatedRole = Gatekeeper::deactivateRole('product_manager');

// or using an enum...

enum Role: string {
    case ProjectManager = 'product_manager';
}

$deactivatedRole = Gatekeeper::deactivateRole(Role::ProjectManager);
```

<a name="reactivate-role"></a>
### Reactivate Role

You may reactivate an inactive role to resume granting access to models.

The `reactivateRole` method accepts a `RolePacket` instance, a string, or a string-backed enum.

If the role does not exist, a `RoleNotFoundException` will be thrown.

If the role is already active, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$reactivatedRole = Gatekeeper::reactivateRole('user_manager');

// or using an enum...

enum Role: string {
    case UserManager = 'user_manager';
}

$reactivatedRole = Gatekeeper::reactivateRole(Role::UserManager);
```

<a name="delete-role"></a>
### Delete Role

You may delete a role to remove it from your application.

> [!WARNING]
> Deleting a role will remove it from your application and unassign it from all models.

The `deleteRole` method accepts a `RolePacket` instance, a string, or a string-backed enum.

If the role is already deleted, the method will return `true` without raising an exception.

**Returns:** `bool`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$roleDeleted = Gatekeeper::deleteRole('product_supervisor');

// or using an enum...

enum Role: string {
    case ProjectsSupervisor = 'product_supervisor';
}

$roleDeleted = Gatekeeper::deleteRole(Role::ProjectsSupervisor);
```

<a name="find-role-by-name"></a>
### Find Role by Name

You may retrieve a role by its name. If the role does not exist, `null` will be returned.

The `findRoleByName` method accepts a string or a string-backed enum.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket|null`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$role = Gatekeeper::findRoleByName('product_manager');

// or using an enum...

enum Role: string {
    case ProjectManager = 'product_manager';
}

$role = Gatekeeper::findRoleByName(Role::ProjectManager);
```

<a name="get-all-roles"></a>
### Get All Roles

You may retrieve a collection of all roles defined in your application, regardless of their active status.

The `getAllRoles` method does not take any arguments.

**Returns:** `\Illuminate\Support\Collection<string, \Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$roles = Gatekeeper::getAllRoles();
```

<a name="model-role-relationships"></a>
## Model Role Relationships

The following methods allow you to assign, unassign, deny, undeny, and inspect roles for models.

> [!NOTE]
> Models passed to Gatekeeper must use the `\Gillyware\Gatekeeper\Traits\HasRoles` trait to enable the methods described below.

<a name="assign-role-to-model"></a>
### Assign Role to Model

You may assign a role to a model using one of the following approaches:

- Using the static `Gatekeeper::assignRoleToModel($model, $role)` method
- Using the fluent `Gatekeeper::for($model)->assignRole($role)` chain
- Calling `$model->assignRole($role)` directly (available via the `HasRoles` trait)

The `$role` argument must be a `RolePacket` instance, a string, or a string-backed enum.

If the role does not exist, a `RoleNotFoundException` will be thrown.

If the role is denied from a model, the denial will be removed before assigning.

**Returns:** `bool` – `true` if the role is assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case ProjectManager = 'product_manager';
}

$user = User::query()->findOrFail(1);

$roleAssigned = Gatekeeper::assignRoleToModel($user, Role::ProjectManager);

// or fluently...

$roleAssigned = Gatekeeper::for($user)->assignRole(Role::ProjectManager);

// or via the trait method...

$roleAssigned = $user->assignRole(Role::ProjectManager);
```

<a name="assign-multiple-roles-to-model"></a>
### Assign Multiple Roles to Model

You may assign multiple roles to a model using one of the following approaches:

- Using the static `Gatekeeper::assignAllRolesToModel($model, $roles)` method
- Using the fluent `Gatekeeper::for($model)->assignAllRoles($roles)` chain
- Calling `$model->assignAllRoles($roles)` directly (available via the `HasRoles` trait)

The `$roles` argument must be an array or Arrayable containing any combination `RolePacket` instances, strings, or a string-backed enums.

If a role is already assigned, it will be skipped without raising an exception.

If a role does not exist, a `RoleNotFoundException` will be thrown.

If a role is denied from a model, the denial will be removed before assigning.

> [!NOTE]
> This method stops on the first failure.

**Returns:** `bool` – `true` if all roles are assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case ProductManager = 'product_manager';
    case ProductOwner = 'product_owner';
    case UserManager = 'user_manager';
}

$roles = [Role::ProductManager, Role::ProductOwner, Role::UserManager];

$user = User::query()->findOrFail(1);

$rolesAssigned = Gatekeeper::assignAllRolesToModel($user, $roles);

// or fluently...

$rolesAssigned = Gatekeeper::for($user)->assignAllRoles($roles);

// or via the trait method...

$rolesAssigned = $user->assignAllRoles($roles);
```

<a name="unassign-role-from-model"></a>
### Unassign Role from Model

You may unassign a role from a model using one of the following approaches:

- Using the static `Gatekeeper::unassignRoleFromModel($model, $role)` method
- Using the fluent `Gatekeeper::for($model)->unassignRole($role)` chain
- Calling `$model->unassignRole($role)` directly (available via the `HasRoles` trait)

The `$role` argument must be a `RolePacket` instance, a string, or a string-backed enum.

If the role does not exist, a `RoleNotFoundException` will be thrown.

If the role is denied from the model, the denial will remain intact.

**Returns:** bool – `true` if the role is not assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case UserManager = 'user_manager';
}

$user = User::query()->findOrFail(1);

$roleUnassigned = Gatekeeper::unassignRoleFromModel($user, Role::UserManager);

// or fluently...

$roleUnassigned = Gatekeeper::for($user)->unassignRole(Role::UserManager);

// or via the trait method...

$roleUnassigned = $user->unassignRole(Role::UserManager);
```

<a name="unassign-multiple-roles-from-model"></a>
### Unassign Multiple Roles from Model

You may unassign multiple roles from a model using one of the following approaches:

- Using the static `Gatekeeper::unassignAllRolesFromModel($model, $roles)` method
- Using the fluent `Gatekeeper::for($model)->unassignAllRoles($roles)` chain
- Calling `$model->unassignAllRoles($roles)` directly (available via the `HasRoles` trait)

The `$roles` argument must be an array or Arrayable containing any combination `RolePacket` instances, strings, or a string-backed enums.

If a role is already unassigned, it will be skipped without raising an exception.

If a role does not exist, a `RoleNotFoundException` will be thrown.

If the role is denied from the model, the denial will remain intact.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if none of the roles are assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case ProductManager = 'product_manager';
    case ProductOwner = 'product_owner';
    case UserManager = 'user_manager';
}

$roles = [Role::ProductManager, Role::ProductOwner, Role::UserManager];

$user = User::query()->findOrFail(1);

$rolesUnassigned = Gatekeeper::unassignAllRolesFromModel($user, $roles);

// or fluently...

$rolesUnassigned = Gatekeeper::for($user)->unassignAllRoles($roles);

// or via the trait method...

$rolesUnassigned = $user->unassignAllRoles($roles);
```

<a name="deny-role-from-model"></a>
### Deny Role from Model

To deny a role from a model means to block access to a role even if the role is granted by default or inherited from a team.

You may deny a role from a model using one of the following approaches:

- Using the static `Gatekeeper::denyRoleFromModel($model, $role)` method
- Using the fluent `Gatekeeper::for($model)->denyRole($role)` chain
- Calling `$model->denyRole($role)` directly (available via the `HasRoles` trait)

The `$role` argument must be a `RolePacket` instance, a string, or a string-backed enum.

If the role does not exist, a `RoleNotFoundException` will be thrown.

If the role is assigned to the model, the role will be unassigned from the model before denying.

**Returns:** bool – `true` if the role is denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case UserManager = 'user_manager';
}

$user = User::query()->findOrFail(1);

$roleDenied = Gatekeeper::denyRoleFromModel($user, Role::UserManager);

// or fluently...

$roleDenied = Gatekeeper::for($user)->denyRole(Role::UserManager);

// or via the trait method...

$roleDenied = $user->denyRole(Role::UserManager);
```

<a name="deny-multiple-roles-from-model"></a>
### Deny Multiple Roles from Model

You may deny multiple roles from a model using one of the following approaches:

- Using the static `Gatekeeper::denyAllRolesFromModel($model, $roles)` method
- Using the fluent `Gatekeeper::for($model)->denyAllRoles($roles)` chain
- Calling `$model->denyAllRoles($roles)` directly (available via the `HasRoles` trait)

The `$roles` argument must be an array or Arrayable containing any combination `RolePacket` instances, strings, or a string-backed enums.

If a role is already denied, it will be skipped without raising an exception.

If a role does not exist, a `RoleNotFoundException` will be thrown.

If the role is denied from the model, the denial will remain intact.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if all roles are denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case ProjectManager = 'project_manager';
    case ProjectOwner = 'project_owner';
    case ScrumMaster = 'scrum_master';
}

$roles = [Role::ProjectManager, Role::ProjectOwner, Role::ScrumMaster];

$user = User::query()->findOrFail(1);

$rolesDenied = Gatekeeper::denyAllRolesFromModel($user, $roles);

// or fluently...

$rolesDenied = Gatekeeper::for($user)->denyAllRoles($roles);

// or via the trait method...

$rolesDenied = $user->denyAllRoles($roles);
```

<a name="undeny-role-from-model"></a>
### Undeny Role from Model

To undeny a role from a model means to unblock access to a role, allowing acces if the role is granted by default, directly assigned, or inherited from a team.

You may undeny a role from a model using one of the following approaches:

- Using the static `Gatekeeper::undenyRoleFromModel($model, $role)` method
- Using the fluent `Gatekeeper::for($model)->undenyRole($role)` chain
- Calling `$model->undenyRole($role)` directly (available via the `HasRoles` trait)

The `$role` argument must be a `RolePacket` instance, a string, or a string-backed enum.

If the role does not exist, a `RoleNotFoundException` will be thrown.

**Returns:** bool – `true` if the role is not denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case UserManager = 'user_manager';
}

$user = User::query()->findOrFail(1);

$roleUndenied = Gatekeeper::undenyRoleFromModel($user, Role::UserManager);

// or fluently...

$roleUndenied = Gatekeeper::for($user)->undenyRole(Role::UserManager);

// or via the trait method...

$roleUndenied = $user->undenyRole(Role::UserManager);
```

<a name="undeny-multiple-roles-from-model"></a>
### Undeny Multiple Roles from Model

You may undeny multiple roles from a model using one of the following approaches:

- Using the static `Gatekeeper::undenyAllRolesFromModel($model, $roles)` method
- Using the fluent `Gatekeeper::for($model)->undenyAllRoles($roles)` chain
- Calling `$model->undenyAllRoles($roles)` directly (available via the `HasRoles` trait)

The `$roles` argument must be an array or Arrayable containing any combination `RolePacket` instances, strings, or a string-backed enums.

If a role is not denied, it will be skipped without raising an exception.

If a role does not exist, a `RoleNotFoundException` will be thrown.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if none of the roles are denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case ProjectManager = 'project_manager';
    case ProjectOwner = 'project_owner';
    case ScrumMaster = 'scrum_master';
}

$roles = [Role::ProjectManager, Role::ProjectOwner, Role::ScrumMaster];

$user = User::query()->findOrFail(1);

$rolesUndenied = Gatekeeper::undenyAllRolesFromModel($user, $roles);

// or fluently...

$rolesUndenied = Gatekeeper::for($user)->undenyAllRoles($roles);

// or via the trait method...

$rolesUndenied = $user->undenyAllRoles($roles);
```

<a name="check-model-has-role"></a>
### Check Model Has Role

A model may have an undenied, active role granted by default, directly assigned, or indirectly assigned through a team. This method checks all sources to determine if the model has access to the role.

You may check if a model has a role using one of the following approaches:

- Using the static `Gatekeeper::modelHasRole($model, $role)` method
- Using the fluent `Gatekeeper::for($model)->hasRole($role)` chain
- Calling `$model->hasRole($role)` directly (available via the `HasRoles` trait)

The `$role` argument must be a `RolePacket` instance, a string, or a string-backed enum.

If the role does not exist, `false` will be returned.

**Returns:** bool – `true` if the model has access to the given role

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case UserManager = 'user_manager';
}

$user = User::query()->findOrFail(1);

$hasRole = Gatekeeper::modelHasRole($user, Role::UserManager);

// or fluently...

$hasRole = Gatekeeper::for($user)->hasRole(Role::UserManager);

// or via the trait method...

$hasRole = $user->hasRole(Role::UserManager);
```

<a name="check-model-has-any-role"></a>
### Check Model Has Any Role

A model may have an undenied, active role granted by default, directly assigned, or indirectly assigned through a team. This method checks all sources to determine if the model has access to any of the given roles.

You may check if a model has any of a set of roles using one of the following approaches:

- Using the static `Gatekeeper::modelHasAnyRole($model, $roles)` method
- Using the fluent `Gatekeeper::for($model)->hasAnyRole($roles)` chain
- Calling `$model->hasAnyRole($roles)` directly (available via the `HasRoles` trait)

The `$roles` argument must be an array or Arrayable containing any combination `RolePacket` instances, strings, or a string-backed enums.

If the role does not exist, it will be skipped.

**Returns:** bool – `true` if the model has access to any of the given roles

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case ProductManager = 'product_manager';
    case ProductOwner = 'product_owner';
    case UserManager = 'user_manager';
}

$roles = [Role::ProductManager, Role::ProductOwner, Role::UserManager];

$user = User::query()->findOrFail(1);

$hasAnyRole = Gatekeeper::modelHasAnyRole($user, $roles);

// or fluently...

$hasAnyRole = Gatekeeper::for($user)->hasAnyRole($roles);

// or via the trait method...

$hasAnyRole = $user->hasAnyRole($roles);
```

<a name="check-model-has-all-roles"></a>
### Check Model Has All Roles

A model may have an undenied, active role granted by default, directly assigned, or indirectly assigned through a team. This method checks all sources to determine if the model has access to all of the given roles.

You may check if a model has all of a set of roles using one of the following approaches:

- Using the static `Gatekeeper::modelHasAllRoles($model, $roles)` method
- Using the fluent `Gatekeeper::for($model)->hasAllRoles($roles)` chain
- Calling `$model->hasAllRoles($roles)` directly (available via the `HasRoles` trait)

The `$roles` argument must be an array or Arrayable containing any combination `RolePacket` instances, strings, or a string-backed enums.

If the role does not exist, `false` will be returned.

**Returns:** bool – `true` if the model has access to all of the given roles

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case ProductManager = 'product_manager';
    case ProductOwner = 'product_owner';
    case UserManager = 'user_manager';
}

$roles = [Role::ProductManager, Role::ProductOwner, Role::UserManager];

$user = User::query()->findOrFail(1);

$hasAllRoles = Gatekeeper::modelHasAllRoles($user, $roles);

// or fluently...

$hasAllRoles = Gatekeeper::for($user)->hasAllRoles($roles);

// or via the trait method...

$hasAllRoles = $user->hasAllRoles($roles);
```

<a name="get-direct-roles-for-model"></a>
### Get Direct Roles for Model

You may retrieve a collection of all roles directly assigned to a given model, regardless of their active status. This does not include roles inherited from teams.

You may get a model's direct roles using one of the following approaches:

- Using the static `Gatekeeper::getDirectRolesForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getDirectRoles()` chain
- Calling `$model->getDirectRoles()` directly (available via the `HasRoles` trait)

**Returns:** `\Illuminate\Support\Collection<string, \Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$directRoles = Gatekeeper::getDirectRolesForModel($user);

// or fluently...

$directRoles = Gatekeeper::for($user)->getDirectRoles();

// or via the trait method...

$directRoles = $user->getDirectRoles();
```

<a name="get-effective-roles-for-model"></a>
### Get Effective Roles for Model

You may retrieve a collection of all active roles effectively assigned to a given model, including those inherited from teams.

You may get a model's effective roles using one of the following approaches:

- Using the static `Gatekeeper::getEffectiveRolesForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getEffectiveRoles()` chain
- Calling `$model->getEffectiveRoles()` directly (available via the `HasRoles` trait)

**Returns:** `\Illuminate\Support\Collection<string, \Gillyware\Gatekeeper\Packets\Entities\Role\RolePacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$effectiveRoles = Gatekeeper::getEffectiveRolesForModel($user);

// or fluently...

$effectiveRoles = Gatekeeper::for($user)->getEffectiveRoles();

// or via the trait method...

$effectiveRoles = $user->getEffectiveRoles();
```

<a name="get-verbose-roles-for-model"></a>
### Get Verbose Roles for Model

You may retrieve a collection of all active roles effectively assigned to a given model, along with the source(s) of each role (ex., direct or via team).

You may get a model's verbose roles using one of the following approaches:

- Using the static `Gatekeeper::getVerboseRolesForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getVerboseRoles()` chain
- Calling `$model->getVerboseRoles()` directly (available via the `HasRoles` trait)

**Returns:** `\Illuminate\Support\Collection`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$verboseRoles = Gatekeeper::getVerboseRolesForModel($user);

// or fluently...

$verboseRoles = Gatekeeper::for($user)->getVerboseRoles();

// or via the trait method...

$verboseRoles = $user->getVerboseRoles();
```

<a name="next-steps"></a>
## Next Steps

Entities:
- [Permissions](permissions.md)
- [Features](features.md)
- [Teams](teams.md)

Control Access with Entities:
- [Middleware](middleware.md)
- [Blade Directives](blade-directives.md)

Manage Entities and Assignments:
- [Artisan Commands](artisan-commands.md)

Track Entity and Entity Assignment Changes:
- [Audit Logging](audit-logging.md)
