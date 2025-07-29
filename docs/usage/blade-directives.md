# Blade Directives

- [Permission Directives](#permission-directives)
- [Role Directives](#role-directives)
- [Feature Directives](#feature-directives)
- [Team Directives](#team-directives)
- [Next Steps](#next-steps)

Gatekeeper provides several Blade directives to simplify conditional rendering based on a user's permissions, roles, features, or team assignments. Each directive can be used in two ways:

- With a model explicitly provided

- Without a model, in which case `Auth::user()` will be used as the model

<a name="permission-directives"></a>
## Permission Directives

> [!NOTE]
> The parameter types match the corresponding functions [here](permissions.md#check-model-has-permission).

`@hasPermission($permission)`

`@hasPermission($model, $permission)`

Renders the enclosed content if the model has the specified permission.

```blade
@hasPermission('edit-posts')
    <button>Edit Post</button>
@endhasPermission

@hasPermission($user, 'edit-posts')
    <button>Edit Post</button>
@endhasPermission
```

`@hasAnyPermission($permission)`

`@hasAnyPermission($model, $permission)`

Renders if the model has any of the given permissions.

```blade
@hasAnyPermission(['edit-posts', 'delete-posts'])
    <p>You can edit or delete posts.</p>
@endhasAnyPermission

@hasAnyPermission($user, ['edit-posts', 'delete-posts'])
    <p>User can edit or delete posts.</p>
@endhasAnyPermission
```

`@hasAllPermissions($permission)`

`@hasAllPermissions($model, $permission)`

Renders if the model has all of the given permissions.

```blade
@hasAllPermissions(['edit-posts', 'publish-posts'])
    <p>You can edit and publish posts.</p>
@endhasAllPermissions

@hasAllPermissions($user, ['edit-posts', 'publish-posts'])
    <p>User has both permissions.</p>
@endhasAllPermissions
```

<a name="role-directives"></a>
## Role Directives

> [!NOTE]
> The parameter types match the corresponding functions [here](roles.md#check-model-has-role).

`@hasRole($role)`

`@hasRole($model, $role)`

Renders if the model has the specified role.

```blade
@hasRole('admin')
    <p>Welcome, admin!</p>
@endhasRole
```

`@hasAnyRole($roles)`

`@hasAnyRole($model, $roles)`

Renders if the model has any of the given roles.

```blade
@hasAnyRole(['admin', 'editor'])
    <p>You have access to manage content.</p>
@endhasAnyRole
```

`@hasAllRoles($roles)`

`@hasAllRoles($model, $roles)`

Renders if the model has all of the given roles.

```blade
@hasAllRoles(['editor', 'reviewer'])
    <p>You can edit and review content.</p>
@endhasAllRoles
```

<a name="feature-directives"></a>
## Feature Directives

> [!NOTE]
> The parameter types match the corresponding functions [here](features.md#check-model-has-feature).

`@hasFeature($feature)`

`@hasFeature($model, $feature)`

Renders if the model has the specified feature.

```blade
@hasFeature('child_tracking')
    <p>Welcome!</p>
@endhasFeature
```

`@hasAnyFeature($features)`

`@hasAnyFeature($model, $features)`

Renders if the model has any of the given features.

```blade
@hasAnyFeature(['pumping_tracking', 'child_tracking'])
    <p>You have access.</p>
@endhasAnyFeature
```

`@hasAllFeatures($features)`

`@hasAllFeatures($model, $features)`

Renders if the model has all of the given features.

```blade
@hasAllFeatures(['pumping_tracking', 'child_tracking'])
    <p>Hey there!</p>
@endhasAllFeatures
```

<a name="team-directives"></a>
## Team Directives

> [!NOTE]
> The parameter types match the corresponding functions [here](teams.md#check-model-on-team).

`@onTeam($team)`

`@onTeam($model, $team)`

Renders if the model is on the specified team.

```blade
@onTeam('support')
    <p>You’re part of the support team.</p>
@endonTeam
```

`@onAnyTeam($teams)`

`@onAnyTeam($model, $teams)`

Renders if the model is on any of the given teams.

```blade
@onAnyTeam(['support', 'engineering'])
    <p>You belong to one of the teams.</p>
@endonAnyTeam
```

`@onAllTeams($teams)`

`@onAllTeams($model, $teams)`

Renders if the model is on all of the given teams.

```blade
@onAllTeams(['engineering', 'management'])
    <p>You’re on both teams.</p>
@endonAllTeams
```

<a name="next-steps"></a>
## Next Steps

Entities:
- [Permissions](permissions.md)
- [Roles](roles.md)
- [Features](features.md)
- [Teams](teams.md)

Control Access with Entities:
- [Middleware](middleware.md)

Manage Entities and Assignments:
- [Artisan Commands](artisan-commands.md)

Track Entity and Entity Assignment Changes:
- [Audit Logging](audit-logging.md)
