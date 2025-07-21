# Roles

- [Role Entities](#role-entities)
    - [Check Role Existence](#check-role-existence)
    - [Create Role](#create-role)
    - [Update Role](#update-role)
    - [Deactivate Role](#deactivate-role)
    - [Reactivate Role](#reactivate-role)
    - [Delete Role](#delete-role)
    - [Find Role by Name](#find-role-by-name)
    - [Get All Roles](#get-all-roles)
- [Model Role Assignments](#model-role-assignments)
    - [Assign Role to Model](#assign-role-to-model)
    - [Assign Multiple Roles to Model](#assign-multiple-roles-to-model)
    - [Revoke Role from Model](#revoke-role-from-model)
    - [Revoke Multiple Roles from Model](#revoke-multiple-roles-from-model)
    - [Check Model Has Role](#check-model-has-role)
    - [Check Model Has Any Role](#check-model-has-any-role)
    - [Check Model Has All Roles](#check-model-has-all-roles)
    - [Get Direct Roles for Model](#get-direct-roles-for-model)
    - [Get Effective Roles for Model](#get-effective-roles-for-model)
    - [Get Verbose Roles for Model](#get-verbose-roles-for-model)
- [Next Steps](#next-steps)

A role is a named grouping of permissions. You can assign roles directly to any (configured) model and to any team. A model’s effective roles include those from roles assigned to it directly and from roles attached to its teams.

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

$exists = Gatekeeper::roleExists('users.manager');

// or using an enum...

enum Role: string {
    case UserManager = 'users.manager';
}

$exists = Gatekeeper::roleExists(Role::UserManager);
```

<a name="create-role"></a>
### Create Role

You may create a new role, which will be active by default.

If the role already exists, a `RoleAlreadyExistsException` will be thrown.

The `createRole` method accepts a string or a string-backed enum.

**Returns:** `\Gillyware\Gatekeeper\Packets\RolePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$role = Gatekeeper::createRole('product.manager');

// or using an enum...

enum Role: string {
    case ProductManager = 'product.manager';
}

$role = Gatekeeper::createRole(Role::ProductManager);
```

<a name="update-role"></a>
### Update Role

You may update the name of an existing role.

The `updateRole` method accepts a `RolePacket` instance, a string, or a string-backed enum as the first argument (the existing role), and a string or string-backed enum as the second argument (the new name).

If the role does not exist, a `RoleNotFoundException` will be thrown.

If a role with the new name already exists, a `RoleAlreadyExistsException` will be thrown.

**Returns:** `\Gillyware\Gatekeeper\Packets\RolePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$updatedRole = Gatekeeper::updateRole('product.manager', 'product.owner');

// or using enums...

enum Role: string {
    case ProductManager = 'product.manager';
    case ProductOwner = 'product.owner';
}

$updatedRole = Gatekeeper::updateRole(Role::ProductManager, Role::ProductOwner);
```

<a name="deactivate-role"></a>
### Deactivate Role

You may temporarily deactivate a role if you want it to stop granting access without revoking it from models.

Deactivated roles remain in the database but are ignored by role checks until reactivated. The permissions attached to roles will also be ignored until the role is reactivated.

The `deactivateRole` method accepts a `RolePacket` instance, a string, or a string-backed enum.

If the role does not exist, a `RoleNotFoundException` will be thrown.

If the role is already inactive, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\RolePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$deactivatedRole = Gatekeeper::deactivateRole('projects.manager');

// or using an enum...

enum Role: string {
    case ProjectManager = 'projects.manager';
}

$deactivatedRole = Gatekeeper::deactivateRole(Role::ProjectManager);
```

<a name="reactivate-role"></a>
### Reactivate Role

You may reactivate an inactive role to resume granting access to models.

The `reactivateRole` method accepts a `RolePacket` instance, a string, or a string-backed enum.

If the role does not exist, a `RoleNotFoundException` will be thrown.

If the role is already active, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\RolePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$reactivatedRole = Gatekeeper::reactivateRole('users.manager');

// or using an enum...

enum Role: string {
    case UserManager = 'users.manager';
}

$reactivatedRole = Gatekeeper::reactivateRole(Role::UserManager);
```

<a name="delete-role"></a>
### Delete Role

You may delete a role to remove it from your application.

> [!WARNING]
> Deleting a role will remove it from your application and unassign it from all models.

The `deleteRole` method accepts a `RolePacket` instance, a string, or a string-backed enum.

If the role does not exist, a `RoleNotFoundException` will be thrown.

If the role is already deleted, the method will return `true` without raising an exception.

**Returns:** `bool`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$roleDeleted = Gatekeeper::deleteRole('projects.supervisor');

// or using an enum...

enum Role: string {
    case ProjectsSupervisor = 'projects.supervisor';
}

$roleDeleted = Gatekeeper::deleteRole(Role::ProjectsSupervisor);
```

<a name="find-role-by-name"></a>
### Find Role by Name

You may retrieve a role by its name. If the role does not exist, `null` will be returned.

The `findRoleByName` method accepts a string or a string-backed enum.

**Returns:** `\Gillyware\Gatekeeper\Packets\RolePacket|null`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$role = Gatekeeper::findRoleByName('projects.manager');

// or using an enum...

enum Role: string {
    case ProjectManager = 'projects.manager';
}

$role = Gatekeeper::findRoleByName(Role::ProjectManager);
```

<a name="get-all-roles"></a>
### Get All Roles

You may retrieve a collection of all roles defined in your application, regardless of their active status.

The `getAllRoles` method does not take any arguments.

**Returns:** `\Illuminate\Support\Collection<\Gillyware\Gatekeeper\Packets\RolePacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$roles = Gatekeeper::getAllRoles();
```

<a name="model-role-assignments"></a>
## Model Role Assignments

The following methods allow you to assign, revoke, and inspect roles for models.

> [!NOTE]
> Models passed to Gatekeeper must use the `\Gillyware\Gatekeeper\Traits\HasRoles` trait to enable the methods described below.

<a name="assign-role-to-model"></a>
### Assign Role to Model

You may assign a role to a model using one of the following approaches:

- Using the static `Gatekeeper::assignRoleToModel($model, $role)` method
- Using the fluent `Gatekeeper::for($model)->assignRole($role)` chain
- Calling `$model->assignRole($role)` directly (available via the `HasRoles` trait)

The `$role` argument can be a:

- `RolePacket` instance
- string (e.g. `'users.manager'`)
- string-backed enum value

If the role does not exist, a `RoleNotFoundException` will be thrown.

**Returns:** `bool` – `true` if the role was newly assigned or already present

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case ProjectManager = 'projects.manager';
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

The `$roles` argument must be an array or Arrayable containing any combination of:

- `RolePacket` instance
- string (e.g. `'users.manager'`)
- string-backed enum value

If a role is already assigned, it will be skipped without raising an exception.

If a role does not exist, a `RoleNotFoundException` will be thrown.

> [!NOTE]
> This method stops on the first failure.

**Returns:** `bool` – `true` if all roles were successfully assigned or already present

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case ProductManager = 'products.manager';
    case ProductOwner = 'products.owner';
    case UserManager = 'users.manager';
}

$roles = [Role::ProductManager, Role::ProductOwner, Role::UserManager];

$user = User::query()->findOrFail(1);

$rolesAssigned = Gatekeeper::assignAllRolesToModel($user, $roles);

// or fluently...

$rolesAssigned = Gatekeeper::for($user)->assignAllRoles($roles);

// or via the trait method...

$rolesAssigned = $user->assignAllRoles($roles);
```

<a name="revoke-role-from-model"></a>
### Revoke Role from Model

You may revoke a role from a model using one of the following approaches:

- Using the static `Gatekeeper::revokeRoleFromModel($model, $role)` method
- Using the fluent `Gatekeeper::for($model)->revokeRole($role)` chain
- Calling `$model->revokeRole($role)` directly (available via the `HasRoles` trait)

The `$role` argument can be a:

- `RolePacket` instance
- string (e.g. `'users.manager'`)
- string-backed enum value

If the role does not exist, a `RoleNotFoundException` will be thrown.

**Returns:** bool – `true` if the role was removed or was not previously assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case UserManager = 'users.manager';
}

$user = User::query()->findOrFail(1);

$roleRevoked = Gatekeeper::revokeRoleFromModel($user, Role::UserManager);

// or fluently...

$roleRevoked = Gatekeeper::for($user)->revokeRole(Role::UserManager);

// or via the trait method...

$roleRevoked = $user->revokeRole(Role::UserManager);
```

<a name="revoke-multiple-roles-from-model"></a>
### Revoke Multiple Roles from Model

You may revoke multiple roles from a model using one of the following approaches:

- Using the static `Gatekeeper::revokeAllRolesFromModel($model, $roles)` method
- Using the fluent `Gatekeeper::for($model)->revokeAllRoles($roles)` chain
- Calling `$model->revokeAllRoles($roles)` directly (available via the `HasRoles` trait)

The `$roles` argument must be an array or Arrayable containing any combination of:

- `RolePacket` instance
- string (e.g. `'users.manager'`)
- string-backed enum value

If a role is already unassigned, it will be skipped without raising an exception.

If a role does not exist, a `RoleNotFoundException` will be thrown.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if all roles were revoked or were not previously assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case ProductManager = 'products.manager';
    case ProductOwner = 'products.owner';
    case UserManager = 'users.manager';
}

$roles = [Role::ProductManager, Role::ProductOwner, Role::UserManager];

$user = User::query()->findOrFail(1);

$rolesRevoked = Gatekeeper::revokeAllRolesFromModel($user, $roles);

// or fluently...

$rolesRevoked = Gatekeeper::for($user)->revokeAllRoles($roles);

// or via the trait method...

$rolesRevoked = $user->revokeAllRoles($roles);
```

<a name="check-model-has-role"></a>
### Check Model Has Role

A model may have an active role directly assigned or indirectly assigned through a team. This method checks all sources to determine if the model has access to the role.

You may check if a model has a role using one of the following approaches:

- Using the static `Gatekeeper::modelHasRole($model, $role)` method
- Using the fluent `Gatekeeper::for($model)->hasRole($role)` chain
- Calling `$model->hasRole($role)` directly (available via the `HasRoles` trait)

The `$role` argument can be a:

- `RolePacket` instance
- string (e.g. `'users.manager'`)
- string-backed enum value

If the role does not exist, `false` will be returned.

**Returns:** bool – `true` if the model has access to the given role, `false` otherwise (including if the model is missing the `HasRoles` trait, the role does not exist, or the role is inactive)

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case UserManager = 'users.manager';
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

A model may have an active role directly assigned or indirectly assigned through a team. This method checks all sources to determine if the model has access to any of the given roles.

You may check if a model has any of a set of roles using one of the following approaches:

- Using the static `Gatekeeper::modelHasAnyRole($model, $roles)` method
- Using the fluent `Gatekeeper::for($model)->hasAnyRole($roles)` chain
- Calling `$model->hasAnyRole($roles)` directly (available via the `HasRoles` trait)

The `$roles` argument must be an array or Arrayable containing any combination of:

- `RolePacket` instance
- string (e.g. `'users.manager'`)
- string-backed enum value

If the role does not exist, it will be skipped.

**Returns:** bool – `true` if the model has access to any of the given roles, `false` otherwise (including if none of the roles exist, are active, or are assigned)

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case ProductManager = 'products.manager';
    case ProductOwner = 'products.owner';
    case UserManager = 'users.manager';
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

A model may have an active role directly assigned or indirectly assigned through a team. This method checks all sources to determine if the model has access to all of the given roles.

You may check if a model has all of a set of roles using one of the following approaches:

- Using the static `Gatekeeper::modelHasAllRoles($model, $roles)` method
- Using the fluent `Gatekeeper::for($model)->hasAllRoles($roles)` chain
- Calling `$model->hasAllRoles($roles)` directly (available via the `HasRoles` trait)

The `$roles` argument must be an array or Arrayable containing any combination of:

- `RolePacket` instance
- string (e.g. `'users.manager'`)
- string-backed enum value

If the role does not exist, `false` will be returned.

**Returns:** bool – `true` if the model has access to all of the given roles, `false` otherwise (including if any of the roles do not exist, are inactive, or are unassigned)

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Role: string {
    case ProductManager = 'products.manager';
    case ProductOwner = 'products.owner';
    case UserManager = 'users.manager';
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

**Returns:** `\Illuminate\Support\Collection<\Gillyware\Gatekeeper\Packets\RolePacket>`

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

**Returns:** `\Illuminate\Support\Collection<\Gillyware\Gatekeeper\Packets\RolePacket>`

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

You may retrieve a collection of all active roles effectively assigned to a given model, along with the source(s) of each role (e.g., direct or via team).

You may get a model's verbose roles using one of the following approaches:

- Using the static `Gatekeeper::getVerboseRolesForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getVerboseRoles()` chain
- Calling `$model->getVerboseRoles()` directly (available via the `HasRoles` trait)

**Returns:** `\Illuminate\Support\Collection<array{name: string, sources: array}>`

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

Now that you've learned how to manage roles, you may explore how to group them using teams:

[Teams](teams.md)
