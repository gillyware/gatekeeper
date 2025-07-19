# Middleware

- [Available Middleware](#available-middleware)
- [Usage in Routes](#usage-in-routes)
- [Handling Access Denials](#handling-access-denials)
- [Next Steps](#next-steps)

Gatekeeper provides a set of route middleware aliases to enforce access control at the HTTP layer based on a model’s permissions, roles, or team memberships.

> [!NOTE]
> The access checking within these middleware relies on the authenticated user being accessible via `Auth::user()`.

<a name="available-middleware"></a>
## Available Middleware

| Middleware Alias        | Description                                           |
|-------------------------|-------------------------------------------------------|
| `has_permission`        | Checks if the user has a specific permission          |
| `has_any_permission`    | Checks if the user has *any* of the given permissions |
| `has_all_permissions`   | Checks if the user has *all* of the given permissions |
| `has_role`              | Checks if the user has a specific role                |
| `has_any_role`          | Checks if the user has *any* of the given roles       |
| `has_all_roles`         | Checks if the user has *all* of the given roles       |
| `on_team`               | Checks if the user is on a specific team              |
| `on_any_team`           | Checks if the user is on *any* of the given teams     |
| `on_all_teams`          | Checks if the user is on *all* of the given teams     |

<a name="usage-in-routes"></a>
## Usage in Routes

### Single Value

```php
Route::get('/dashboard', function () {
    // ...
})->middleware('has_permission:gatekeeper.view');
```

### Multiple Values

Pass multiple arguments by separating them with commas:

```php
Route::post('/posts', function () {
    // ...
})->middleware('has_any_permission:create-posts,edit-posts');
```

### Route Groups

You can apply middleware to groups:

```php
Route::middleware(['has_role:admin'])->group(function () {
    Route::get('/admin', fn () => view('admin'));
});
```

<a name="handling-access-denials"></a>
## Handling Access Denials

If the user does not meet the middleware condition:
- If the request expects JSON, a 400 response is returned with a `{"message": "Access denied."}` payload.
- Otherwise, the request will abort with a `400 Bad Request` status and an "Access denied." message.

<a name="next-steps"></a>
## Next Steps

Now that you're protecting routes and controllers with middleware, you may explore Gatekeeper's Artisan commands:

[Artisan Commands](artisan-commands.md)
