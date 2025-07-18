# Blade Directives

- [Permission Directives](#permission-directives)
- [Role Directives](#role-directives)
- [Team Directives](#team-directives)
- [Next Steps](#next-steps)

Gatekeeper provides several Blade directives to simplify conditional rendering based on a user's permissions, roles, or team assignments. Each directive can be used in two ways:

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

Now that you've learned how to show or hide content in Blade templates, you may want to restrict access at the route or controller level:

[Middleware](middleware.md)
