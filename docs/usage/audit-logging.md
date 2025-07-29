# Audit Logging

- [Setting the Actor](#setting-the-actor)
- [What Is Logged](#what-is-logged)
- [Next Steps](#next-steps)

Gatekeeper can optionally track actions taken on permissions, roles, features, and teams to provide a full audit trail of changes made through the dashboard, Artisan commands, and your application.

> [!WARNING]
> Audit logging must be explicitly enabled in your configuration. See the `features.audit.enabled` setting in your [configuration file](../configuration.md#feature-flags).

<a name="setting-the-actor"></a>
## Setting the Actor

For every action, a model must be specified as the actor. When an actor is set, all following actions for the current request will be attributed to them (unless overridden).

> [!NOTE]
> If an actor is not specified, `Auth::user()` will be used. If there is no authenticated user, and an actor is not specified, an exception will be thrown.

You may attribute an action to a specific model:

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

Gatekeeper::setActor($user);
Gatekeeper::createPermission('users.create');

// or chain them...

Gatekeeper::setActor($user)->deactivatePermission('users.create');
```

Alternatively, you may want to perform an action without attributing it to a specific model. You can attribute actions to the system:

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

Gatekeeper::systemActor();
Gatekeeper::createPermission('users.create');

// or chain them...

Gatekeeper::systemActor()->deactivatePermission('users.create');
```

<a name="what-is-logged"></a>
## What Is Logged

When audit logging is enabled, the following actions are recorded:

### Permissions

- Create / Update Name / Grant by Default / Revoke Default Grant / Deactivate / Reactivate / Delete
- Assign / Unassign / Deny / Undeny

### Roles

- Create / Update Name / Grant by Default / Revoke Default Grant / Deactivate / Reactivate / Delete
- Assign / Unassign / Deny / Undeny

### Features

- Create / Update Name / Grant by Default / Revoke Default Grant / Deactivate / Reactivate / Delete
- Assign / Unassign / Deny / Undeny

### Teams

- Create / Update Name / Grant by Default / Revoke Default Grant / Deactivate / Reactivate / Delete
- Add / Remove / Deny / Undeny

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

Manage Entities and Assignments:
- [Artisan Commands](artisan-commands.md)
