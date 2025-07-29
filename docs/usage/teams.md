# Teams

- [Team Entities](#team-entities)
    - [Check Team Existence](#check-team-existence)
    - [Create Team](#create-team)
    - [Update Team Name](#update-team-name)
    - [Grant Team by Default](#grant-team-by-default)
    - [Revoke Team Default Grant](#revoke-team-default-grant)
    - [Deactivate Team](#deactivate-team)
    - [Reactivate Team](#reactivate-team)
    - [Delete Team](#delete-team)
    - [Find Team by Name](#find-team-by-name)
    - [Get All Teams](#get-all-teams)
- [Model Team Relationships](#model-team-relationships)
    - [Add Model To Team](#add-model-to-team)
    - [Add Model to Multiple Teams](#add-model-to-multiple-teams)
    - [Remove Model from Team](#remove-model-from-team)
    - [Deny Team from Model](#deny-team-from-model)
    - [Deny Multiple Teams from Model](#deny-multiple-teams-from-model)
    - [Undeny Team from Model](#undeny-team-from-model)
    - [Undeny Multiple Teams from Model](#undeny-multiple-teams-from-model)
    - [Check Model on Team](#check-model-on-team)
    - [Check Model on Any Team](#check-model-on-any-team)
    - [Check Model on All Teams](#check-model-on-all-teams)
    - [Get Direct Teams for Model](#get-direct-teams-for-model)
    - [Get Effective Teams for Model](#get-effective-teams-for-model)
    - [Get Verbose Teams for Model](#get-verbose-teams-for-model)
- [Next Steps](#next-steps)

A team is a collection of models used to provide organizational or contextual grouping (ex., department, project, tenant).

By default, created teams are active and not granted by default. 'Active' means the team is actively granting access, and 'not granted by default' means a model must be explicitly assigned to teams.

A model’s effective teams are the union of its teams granted by default and direct teams, excluding the teams directly denied from the model. Keep in mind, a model will effectively be on no teams (granted by default or direct) if the model is not using the `HasTeams` trait.

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

<a name="update-team-name"></a>
### Update Team Name

You may update the name of an existing team.

The `updateTeamName` method accepts a `TeamPacket` instance, a string, or a string-backed enum as the first argument (the existing team), and a string or string-backed enum as the second argument (the new name).

If the team does not exist, a `TeamNotFoundException` will be thrown.

If a team with the new name already exists, a `TeamAlreadyExistsException` will be thrown.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$updatedTeam = Gatekeeper::updateTeamName('management', 'engineering');

// or using enums...

enum Team: string {
    case Management = 'management';
    case Engineering = 'engineering';
}

$updatedTeam = Gatekeeper::updateTeamName(Team::Management, Team::Engineering);
```

<a name="grant-team-by-default"></a>
### Grant Team by Default

You may want a team that most, if not all, models should be on by default. Granting the team by default effectively assigns all models to it, if the models are not denying it.

The `grantTeamByDefault` method accepts a `TeamPacket` instance, a string, or a string-backed enum.

If the team does not exist, a `TeamNotFoundException` will be thrown.

If the team is already granted by default, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$grantedByDefaultTeam = Gatekeeper::grantTeamByDefault('support');

// or using an enum...

enum Team: string {
    case Support = 'support';
}

$grantedByDefaultTeam = Gatekeeper::grantTeamByDefault(Team::DeleteUsers);
```

<a name="revoke-team-default-grant"></a>
### Revoke Team Default Grant

You may decide that a team should not be [granted by default](#grant-team-by-default). You can easily revoke a team's default grant.

The `revokeTeamDefaultGrant` method accepts a `TeamPacket` instance, a string, or a string-backed enum.

If the team does not exist, a `TeamNotFoundException` will be thrown.

If the team is not granted by default, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$nonGrantedByDefaultTeam = Gatekeeper::revokeTeamDefaultGrant('support');

// or using an enum...

enum Team: string {
    case Support = 'support';
}

$nonGrantedByDefaultTeam = Gatekeeper::revokeTeamDefaultGrant(Team::DeleteUsers);
```

<a name="deactivate-team"></a>
### Deactivate Team

You may temporarily deactivate a team if you want it to stop granting access without unassigning it from models.

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

**Returns:** `\Illuminate\Support\Collection<string, \Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$teams = Gatekeeper::getAllTeams();
```

<a name="model-team-relationships"></a>
## Model Team Relationships

The following methods allow you to assign, unassign, deny, undeny, and inspect teams for models.

> [!NOTE]
> Models passed to Gatekeeper must use the `\Gillyware\Gatekeeper\Traits\HasTeams` trait to enable the methods described below.

<a name="add-model-to-team"></a>
### Add Model To Team

You may add a model to a team using one of the following approaches:

- Using the static `Gatekeeper::addModelToTeam($model, $team)` method
- Using the fluent `Gatekeeper::for($model)->addToTeam($team)` chain
- Calling `$model->addToTeam($team)` directly (available via the `HasTeams` trait)

The `$team` argument must be a `TeamPacket` instance, a string, or a string-backed enum.

If the team does not exist, a `TeamNotFoundException` will be thrown.

If the team is denied from a model, the denial will be removed before assigning.

**Returns:** `bool` – `true` if the model is on the team

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

The `$teams` argument must be an array or Arrayable containing any combination `TeamPacket` instances, strings, or a string-backed enums.

If the model is already on a team, the team will be skipped without raising an exception.

If a team does not exist, a `TeamNotFoundException` will be thrown.

If a team is denied from a model, the denial will be removed before assigning.

> [!NOTE]
> This method stops on the first failure.

**Returns:** `bool` – `true` if the model is on all the teams

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

The `$team` argument must be a `TeamPacket` instance, a string, or a string-backed enum.

If the team does not exist, a `TeamNotFoundException` will be thrown.

If the team is denied from the model, the denial will remain intact.

**Returns:** bool – `true` if the model is not on the team

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

The `$teams` argument must be an array or Arrayable containing any combination `TeamPacket` instances, strings, or a string-backed enums.

If a team is already unassigned, it will be skipped without raising an exception.

If a team does not exist, a `TeamNotFoundException` will be thrown.

If the team is denied from the model, the denial will remain intact.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if the model is not on any of the teams

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

<a name="deny-team-from-model"></a>
### Deny Team from Model

To deny a team from a model means to block access to a team even if the team is granted by default.

You may deny a team from a model using one of the following approaches:

- Using the static `Gatekeeper::denyTeamFromModel($model, $team)` method
- Using the fluent `Gatekeeper::for($model)->denyTeam($team)` chain
- Calling `$model->denyTeam($team)` directly (available via the `HasTeams` trait)

The `$team` argument must be a `TeamPacket` instance, a string, or a string-backed enum.

If the team does not exist, a `TeamNotFoundException` will be thrown.

If the model is on the team, the model will be removed from the model before denying.

**Returns:** bool – `true` if the team is denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Team: string {
    case UserManager = 'user_manager';
}

$user = User::query()->findOrFail(1);

$teamDenied = Gatekeeper::denyTeamFromModel($user, Team::UserManager);

// or fluently...

$teamDenied = Gatekeeper::for($user)->denyTeam(Team::UserManager);

// or via the trait method...

$teamDenied = $user->denyTeam(Team::UserManager);
```

<a name="deny-multiple-teams-from-model"></a>
### Deny Multiple Teams from Model

You may deny multiple teams from a model using one of the following approaches:

- Using the static `Gatekeeper::denyAllTeamsFromModel($model, $teams)` method
- Using the fluent `Gatekeeper::for($model)->denyAllTeams($teams)` chain
- Calling `$model->denyAllTeams($teams)` directly (available via the `HasTeams` trait)

The `$teams` argument must be an array or Arrayable containing any combination `TeamPacket` instances, strings, or a string-backed enums.

If a team is already denied, it will be skipped without raising an exception.

If a team does not exist, a `TeamNotFoundException` will be thrown.

If the team is denied from the model, the denial will remain intact.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if all teams are denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Team: string {
    case ProjectManager = 'project_manager';
    case ProjectOwner = 'project_owner';
    case ScrumMaster = 'scrum_master';
}

$teams = [Team::ProjectManager, Team::ProjectOwner, Team::ScrumMaster];

$user = User::query()->findOrFail(1);

$teamsDenied = Gatekeeper::denyAllTeamsFromModel($user, $teams);

// or fluently...

$teamsDenied = Gatekeeper::for($user)->denyAllTeams($teams);

// or via the trait method...

$teamsDenied = $user->denyAllTeams($teams);
```

<a name="undeny-team-from-model"></a>
### Undeny Team from Model

To undeny a team from a model means to unblock access to a team, allowing acces if the team is granted by default or directly assigned.

You may undeny a team from a model using one of the following approaches:

- Using the static `Gatekeeper::undenyTeamFromModel($model, $team)` method
- Using the fluent `Gatekeeper::for($model)->undenyTeam($team)` chain
- Calling `$model->undenyTeam($team)` directly (available via the `HasTeams` trait)

The `$team` argument must be a `TeamPacket` instance, a string, or a string-backed enum.

If the team does not exist, a `TeamNotFoundException` will be thrown.

**Returns:** bool – `true` if the team is not denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Team: string {
    case UserManager = 'user_manager';
}

$user = User::query()->findOrFail(1);

$teamUndenied = Gatekeeper::undenyTeamFromModel($user, Team::UserManager);

// or fluently...

$teamUndenied = Gatekeeper::for($user)->undenyTeam(Team::UserManager);

// or via the trait method...

$teamUndenied = $user->undenyTeam(Team::UserManager);
```

<a name="undeny-multiple-teams-from-model"></a>
### Undeny Multiple Teams from Model

You may undeny multiple teams from a model using one of the following approaches:

- Using the static `Gatekeeper::undenyAllTeamsFromModel($model, $teams)` method
- Using the fluent `Gatekeeper::for($model)->undenyAllTeams($teams)` chain
- Calling `$model->undenyAllTeams($teams)` directly (available via the `HasTeams` trait)

The `$teams` argument must be an array or Arrayable containing any combination `TeamPacket` instances, strings, or a string-backed enums.

If a team is not denied, it will be skipped without raising an exception.

If a team does not exist, a `TeamNotFoundException` will be thrown.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if none of the teams are denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Team: string {
    case ProjectManager = 'project_manager';
    case ProjectOwner = 'project_owner';
    case ScrumMaster = 'scrum_master';
}

$teams = [Team::ProjectManager, Team::ProjectOwner, Team::ScrumMaster];

$user = User::query()->findOrFail(1);

$teamsUndenied = Gatekeeper::undenyAllTeamsFromModel($user, $teams);

// or fluently...

$teamsUndenied = Gatekeeper::for($user)->undenyAllTeams($teams);

// or via the trait method...

$teamsUndenied = $user->undenyAllTeams($teams);
```

<a name="check-model-on-team"></a>
### Check Model on Team

This method determines if the model is effectively on a given team.

You may check if a model is on a team using one of the following approaches:

- Using the static `Gatekeeper::modelOnTeam($model, $team)` method
- Using the fluent `Gatekeeper::for($model)->onTeam($team)` chain
- Calling `$model->onTeam($team)` directly (available via the `HasTeams` trait)

The `$team` argument must be a `TeamPacket` instance, a string, or a string-backed enum.

If the team does not exist, `false` will be returned.

**Returns:** bool – `true` if the model is on the given team

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

The `$teams` argument must be an array or Arrayable containing any combination `TeamPacket` instances, strings, or a string-backed enums.

If the team does not exist, it will be skipped.

**Returns:** bool – `true` if the model is on any of the given teams

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

The `$teams` argument must be an array or Arrayable containing any combination `TeamPacket` instances, strings, or a string-backed enums.

If the team does not exist, `false` will be returned.

**Returns:** bool – `true` if the model is on all of the given teams

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

<a name="get-direct-teams-for-model"></a>
### Get Direct Teams for Model

You may retrieve a collection of all teams directly assigned to a given model, regardless of their active status.

You may get a model's direct teams using one of the following approaches:

- Using the static `Gatekeeper::getDirectTeamsForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getDirectTeams()` chain
- Calling `$model->getDirectTeams()` directly (available via the `HasTeams` trait)

**Returns:** `\Illuminate\Support\Collection<string, \Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$directTeams = Gatekeeper::getDirectTeamsForModel($user);

// or fluently...

$directTeams = Gatekeeper::for($user)->getDirectTeams();

// or via the trait method...

$directTeams = $user->getDirectTeams();
```

<a name="get-effective-teams-for-model"></a>
### Get Effective Teams for Model

You may retrieve a collection of all active teams effectively assigned to a given model.

You may get a model's effective teams using one of the following approaches:

- Using the static `Gatekeeper::getEffectiveTeamsForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getEffectiveTeams()` chain
- Calling `$model->getEffectiveTeams()` directly (available via the `HasTeams` trait)

**Returns:** `\Illuminate\Support\Collection<string, \Gillyware\Gatekeeper\Packets\Entities\Team\TeamPacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$effectiveTeams = Gatekeeper::getEffectiveTeamsForModel($user);

// or fluently...

$effectiveTeams = Gatekeeper::for($user)->getEffectiveTeams();

// or via the trait method...

$effectiveTeams = $user->getEffectiveTeams();
```

<a name="get-verbose-teams-for-model"></a>
### Get Verbose Teams for Model

You may retrieve a collection of all active teams effectively assigned to a given model, along with the source(s) of each team (ex., direct or granted by default).

You may get a model's verbose teams using one of the following approaches:

- Using the static `Gatekeeper::getVerboseTeamsForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getVerboseTeams()` chain
- Calling `$model->getVerboseTeams()` directly (available via the `HasTeams` trait)

**Returns:** `\Illuminate\Support\Collection`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$verboseTeams = Gatekeeper::getVerboseTeamsForModel($user);

// or fluently...

$verboseTeams = Gatekeeper::for($user)->getVerboseTeams();

// or via the trait method...

$verboseTeams = $user->getVerboseTeams();
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
- [Audit Logging](audit-logging.md)
