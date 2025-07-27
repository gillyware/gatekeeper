# Teams

- [Team Entities](#team-entities)
    - [Check Team Existence](#check-team-existence)
    - [Create Team](#create-team)
    - [Update Team](#update-team)
    - [Deactivate Team](#deactivate-team)
    - [Reactivate Team](#reactivate-team)
    - [Delete Team](#delete-team)
    - [Find Team by Name](#find-team-by-name)
    - [Get All Teams](#get-all-teams)
- [Model Team Assignments](#model-team-assignments)
    - [Add Model To Team](#add-model-to-team)
    - [Add Model to Multiple Teams](#add-model-to-multiple-teams)
    - [Remove Model from Team](#remove-model-from-team)
    - [Remove Model from Multiple Teams](#remove-model-from-multiple-teams)
    - [Check Model on Team](#check-model-on-team)
    - [Check Model on Any Team](#check-model-on-any-team)
    - [Check Model on All Teams](#check-model-on-all-teams)
    - [Get Teams for Model](#get-teams-for-model)
- [Next Steps](#next-steps)

A team is a collection of models used to provide organizational or contextual grouping (e.g., department, project, tenant). You can assign models directly to teams. A team itself may have direct permissions and roles, and every model on that team inherits both the team’s direct permissions and the permissions from the team’s roles.

Gatekeeper exposes a variety of team-related methods through its facade and `HasTeams` trait. This section documents each of them with accompanying code examples.

<a name="team-entities"></a>
## Team Entities

The following methods deal with managing team entities themselves.

<a name="check-team-existence"></a>
### Check Team Existence

You may check whether a team exists in the database, regardless of its active or inactive status.

The `teamExists` method accepts a string or a string-backed enum.

**Returns:** `bool`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$exists = Gatekeeper::teamExists('support');

// or using an enum...

enum Team: string {
    case Support = 'support';
}

$exists = Gatekeeper::teamExists(Team::Support);
```

<a name="create-team"></a>
### Create Team

You may create a new team, which will be active by default.

If the team already exists, a `TeamAlreadyExistsException` will be thrown.

The `createTeam` method accepts a string or a string-backed enum.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$team = Gatekeeper::createTeam('management');

// or using an enum...

enum Team: string {
    case Management = 'management';
}

$team = Gatekeeper::createTeam(Team::Management);
```

<a name="update-team"></a>
### Update Team

You may update the name of an existing team.

The `updateTeam` method accepts a `TeamPacket` instance, a string, or a string-backed enum as the first argument (the existing team), and a string or string-backed enum as the second argument (the new name).

If the team does not exist, a `TeamNotFoundException` will be thrown.

If a team with the new name already exists, a `TeamAlreadyExistsException` will be thrown.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$updatedTeam = Gatekeeper::updateTeam('management', 'engineering');

// or using enums...

enum Team: string {
    case Management = 'management';
    case Engineering = 'engineering';
}

$updatedTeam = Gatekeeper::updateTeam(Team::Management, Team::Engineering);
```

<a name="deactivate-team"></a>
### Deactivate Team

You may temporarily deactivate a team if you want it to stop granting access without revoking it from models.

Deactivated teams remain in the database but are ignored by team checks until reactivated. The roles and permissions attached to teams will also be ignored until the team is reactivated.

The `deactivateTeam` method accepts a `TeamPacket` instance, a string, or a string-backed enum.

If the team does not exist, a `TeamNotFoundException` will be thrown.

If the team is already inactive, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$deactivatedTeam = Gatekeeper::deactivateTeam('finance');

// or using an enum...

enum Team: string {
    case Finance = 'finance';
}

$deactivatedTeam = Gatekeeper::deactivateTeam(Team::Finance);
```

<a name="reactivate-team"></a>
### Reactivate Team

You may reactivate an inactive team to resume granting access to models.

The `reactivateTeam` method accepts a `TeamPacket` instance, a string, or a string-backed enum.

If the team does not exist, a `TeamNotFoundException` will be thrown.

If the team is already active, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$reactivatedTeam = Gatekeeper::reactivateTeam('support');

// or using an enum...

enum Team: string {
    case Support = 'support';
}

$reactivatedTeam = Gatekeeper::reactivateTeam(Team::Support);
```

<a name="delete-team"></a>
### Delete Team

You may delete a team to remove it from your application.

> [!WARNING]
> Deleting a team will remove it from your application and unassign it from all models.

The `deleteTeam` method accepts a `TeamPacket` instance, a string, or a string-backed enum.

If the team does not exist, a `TeamNotFoundException` will be thrown.

If the team is already deleted, the method will return `true` without raising an exception.

**Returns:** `bool`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$teamDeleted = Gatekeeper::deleteTeam('support');

// or using an enum...

enum Team: string {
    case Support = 'support';
}

$teamDeleted = Gatekeeper::deleteTeam(Team::Support);
```

<a name="find-team-by-name"></a>
### Find Team by Name

You may retrieve a team by its name. If the team does not exist, `null` will be returned.

The `findTeamByName` method accepts a string or a string-backed enum.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket|null`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$team = Gatekeeper::findTeamByName('support');

// or using an enum...

enum Team: string {
    case Support = 'support';
}

$team = Gatekeeper::findTeamByName(Team::Support);
```

<a name="get-all-teams"></a>
### Get All Teams

You may retrieve a collection of all teams defined in your application, regardless of their active status.

The `getAllTeams` method does not take any arguments.

**Returns:** `\Illuminate\Support\Collection<\Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$teams = Gatekeeper::getAllTeams();
```

<a name="model-team-assignments"></a>
## Model Team Assignments

The following methods allow you to assign, revoke, and inspect teams for models.

> [!NOTE]
> Models passed to Gatekeeper must use the `\Gillyware\Gatekeeper\Traits\HasTeams` trait to enable the methods described below.

<a name="add-model-to-team"></a>
### Add Model To Team

You may add a model to a team using one of the following approaches:

- Using the static `Gatekeeper::addModelToTeam($model, $team)` method
- Using the fluent `Gatekeeper::for($model)->addToTeam($team)` chain
- Calling `$model->addToTeam($team)` directly (available via the `HasTeams` trait)

The `$team` argument can be a:

- `TeamPacket` instance
- string (e.g. `'support'`)
- string-backed enum value

If the team does not exist, a `TeamNotFoundException` will be thrown.

**Returns:** `bool` – `true` if the model was newly added to or already on the team

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Team: string {
    case Support = 'support';
}

$user = User::query()->findOrFail(1);

$addedToTeam = Gatekeeper::addModelToTeam($user, Team::Support);

// or fluently...

$addedToTeam = Gatekeeper::for($user)->addToTeam(Team::Support);

// or via the trait method...

$addedToTeam = $user->addToTeam(Team::Support);
```

<a name="add-model-to-multiple-teams"></a>
### Add Model to Multiple Teams

You may add a model to multiple teams using one of the following approaches:

- Using the static `Gatekeeper::addModelToAllTeams($model, $teams)` method
- Using the fluent `Gatekeeper::for($model)->addToAllTeams($teams)` chain
- Calling `$model->addToAllTeams($teams)` directly (available via the `HasTeams` trait)

The `$teams` argument must be an array or Arrayable containing any combination of:

- `TeamPacket` instance
- string (e.g. `'support'`)
- string-backed enum value

If the model is already on a team, the team will be skipped without raising an exception.

If a team does not exist, a `TeamNotFoundException` will be thrown.

> [!NOTE]
> This method stops on the first failure.

**Returns:** `bool` – `true` if the model was successfully added to or already on all teams

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Team: string {
    case Management = 'management';
    case Engineering = 'engineering';
    case Support = 'support';
}

$teams = [Team::Management, Team::Engineering, Team::Support];

$user = User::query()->findOrFail(1);

$addedToTeams = Gatekeeper::addModelToAllTeams($user, $teams);

// or fluently...

$addedToTeams = Gatekeeper::for($user)->addToAllTeams($teams);

// or via the trait method...

$addedToTeams = $user->addToAllTeams($teams);
```

<a name="remove-model-from-team"></a>
### Remove Model from Team

You may remove a model from a team using one of the following approaches:

- Using the static `Gatekeeper::removeModelFromTeam($model, $team)` method
- Using the fluent `Gatekeeper::for($model)->removeFromTeam($team)` chain
- Calling `$model->removeFromTeam($team)` directly (available via the `HasTeams` trait)

The `$team` argument can be a:

- `TeamPacket` instance
- string (e.g. `'support'`)
- string-backed enum value

If the team does not exist, a `TeamNotFoundException` will be thrown.

**Returns:** bool – `true` if the model was removed from the team or was never on the team

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Team: string {
    case Support = 'support';
}

$user = User::query()->findOrFail(1);

$removedFromTeam = Gatekeeper::removeModelFromTeam($user, Team::Support);

// or fluently...

$removedFromTeam = Gatekeeper::for($user)->removeFromTeam(Team::Support);

// or via the trait method...

$removedFromTeam = $user->removeFromTeam(Team::Support);
```

<a name="remove-model-from-multiple-teams"></a>
### Remove Model from Multiple Teams

You may remove a model from multiple teams using one of the following approaches:

- Using the static `Gatekeeper::removeModelFromAllTeams($model, $teams)` method
- Using the fluent `Gatekeeper::for($model)->removeFromAllTeams($teams)` chain
- Calling `$model->removeFromAllTeams($teams)` directly (available via the `HasTeams` trait)

The `$teams` argument must be an array or Arrayable containing any combination of:

- `TeamPacket` instance
- string (e.g. `'support'`)
- string-backed enum value

If the model is already not on a team, it will be skipped without raising an exception.

If a team does not exist, a `TeamNotFoundException` will be thrown.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if the model is removed from all teams or was already not on them

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Team: string {
    case Management = 'management';
    case Engineering = 'engineering';
    case Support = 'support';
}

$teams = [Team::Management, Team::Engineering, Team::Support];

$user = User::query()->findOrFail(1);

$removedFromTeams = Gatekeeper::removeModelFromAllTeams($user, $teams);

// or fluently...

$removedFromTeams = Gatekeeper::for($user)->removeFromAllTeams($teams);

// or via the trait method...

$removedFromTeams = $user->removeFromAllTeams($teams);
```

<a name="check-model-on-team"></a>
### Check Model on Team

This method determines if the model is effectively on a given team.

You may check if a model is on a team using one of the following approaches:

- Using the static `Gatekeeper::modelOnTeam($model, $team)` method
- Using the fluent `Gatekeeper::for($model)->onTeam($team)` chain
- Calling `$model->onTeam($team)` directly (available via the `HasTeams` trait)

The `$team` argument can be a:

- `TeamPacket` instance
- string (e.g. `'support'`)
- string-backed enum value

If the team does not exist, `false` will be returned.

**Returns:** bool – `true` if the model is on the given team, `false` otherwise (including if the model is missing the `HasTeams` trait, the team does not exist, or the team is inactive)

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Team: string {
    case Support = 'support';
}

$user = User::query()->findOrFail(1);

$onTeam = Gatekeeper::modelOnTeam($user, Team::Support);

// or fluently...

$onTeam = Gatekeeper::for($user)->onTeam(Team::Support);

// or via the trait method...

$onTeam = $user->onTeam(Team::Support);
```

<a name="check-model-on-any-team"></a>
### Check Model on Any Team

This method determines if the model is effectively on any of the given teams.

You may check if a model is on any of a set of teams using one of the following approaches:

- Using the static `Gatekeeper::modelOnAnyTeam($model, $teams)` method
- Using the fluent `Gatekeeper::for($model)->onAnyTeam($teams)` chain
- Calling `$model->onAnyTeam($teams)` directly (available via the `HasTeams` trait)

The `$teams` argument must be an array or Arrayable containing any combination of:

- `TeamPacket` instance
- string (e.g. `'support'`)
- string-backed enum value

If the team does not exist, it will be skipped.

**Returns:** bool – `true` if the model is on any of the given teams, `false` otherwise (including if none of the teams exist, are active, or are assigned)

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Team: string {
    case Management = 'management';
    case Engineering = 'engineering';
    case Support = 'support';
}

$teams = [Team::Management, Team::Engineering, Team::Support];

$user = User::query()->findOrFail(1);

$onAnyTeam = Gatekeeper::modelOnAnyTeam($user, $teams);

// or fluently...

$onAnyTeam = Gatekeeper::for($user)->onAnyTeam($teams);

// or via the trait method...

$onAnyTeam = $user->onAnyTeam($teams);
```

<a name="check-model-on-all-teams"></a>
### Check Model on All Teams

This method determines if the model is effectively on all of the given teams.

You may check if a model is on all of a set of teams using one of the following approaches:

- Using the static `Gatekeeper::modelOnAllTeams($model, $teams)` method
- Using the fluent `Gatekeeper::for($model)->onAllTeams($teams)` chain
- Calling `$model->onAllTeams($teams)` directly (available via the `HasTeams` trait)

The `$teams` argument must be an array or Arrayable containing any combination of:

- `TeamPacket` instance
- string (e.g. `'support'`)
- string-backed enum value

If the team does not exist, `false` will be returned.

**Returns:** bool – `true` if the model is on all of the given teams, `false` otherwise (including if any of the teams do not exist, are inactive, or are unassigned)

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Team: string {
    case Management = 'management';
    case Engineering = 'engineering';
    case Support = 'support';
}

$teams = [Team::Management, Team::Engineering, Team::Support];

$user = User::query()->findOrFail(1);

$onAllTeams = Gatekeeper::modelOnAllTeams($user, $teams);

// or fluently...

$onAllTeams = Gatekeeper::for($user)->onAllTeams($teams);

// or via the trait method...

$onAllTeams = $user->onAllTeams($teams);
```

<a name="get-teams-for-model"></a>
### Get Teams for Model

You may retrieve a collection of all teams directly assigned to a given model, regardless of their active status.

You may get a model's direct teams using one of the following approaches:

- Using the static `Gatekeeper::getTeamsForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getTeams()` chain
- Calling `$model->getTeams()` directly (available via the `HasTeams` trait)

**Returns:** `\Illuminate\Support\Collection<\Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$teams = Gatekeeper::getTeamsForModel($user);

// or fluently...

$teams = Gatekeeper::for($user)->getTeams();

// or via the trait method...

$teams = $user->getTeams();
```

<a name="next-steps"></a>
## Next Steps

Entities:
- [Permissions](permissions.md)
- [Roles](roles.md)
- [Features](features.md)

Control Access with Entities:
- [Middleware](middleware.md)
- [Blade Directives](blade-directives.md)

Manage Entities and Assignments:
- [Artisan Commands](artisan-commands.md)

Track Entity and Entity Assignment Changes:
- [Audit Logging]('audit-logging.md')
