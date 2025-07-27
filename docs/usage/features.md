# Features

- [Feature Entities](#feature-entities)
    - [Check Feature Existence](#check-feature-existence)
    - [Create Feature](#create-feature)
    - [Update Feature](#update-feature)
    - [Turn Feature Off by Default](#turn-feature-off-by-default)
    - [Turn Feature On by Default](#turn-feature-on-by-default)
    - [Deactivate Feature](#deactivate-feature)
    - [Reactivate Feature](#reactivate-feature)
    - [Delete Feature](#delete-feature)
    - [Find Feature by Name](#find-feature-by-name)
    - [Get All Features](#get-all-features)
- [Model Feature Assignments](#model-feature-assignments)
    - [Turn Feature On for Model](#turn-feature-on-for-model)
    - [Turn Multiple Features On for Model](#turn-multiple-features-on-for-model)
    - [Turn Feature Off for Model](#turn-feature-off-for-model)
    - [Turn Multiple Features Off for Model](#turn-multiple-features-off-for-model)
    - [Check Model Has Feature](#check-model-has-feature)
    - [Check Model Has Any Feature](#check-model-has-any-feature)
    - [Check Model Has All Features](#check-model-has-all-features)
    - [Get Direct Features for Model](#get-direct-features-for-model)
    - [Get Effective Features for Model](#get-effective-features-for-model)
    - [Get Verbose Features for Model](#get-verbose-features-for-model)
- [Next Steps](#next-steps)

A feature is a block of functionality within your application that you may want a subset of users to access. In Gatekeeper features may be directly assigned to any (configured) model and to any team. You may also set features on by default, which would allow all models to access the feature without needing to allow access via assignments. A model's effective features include those from features assigned to it directly, from features attached to its teams, and from features that are on by default.

Gatekeeper exposes a variety of feature-related methods through its facade and `HasFeatures` trait. This section documents each of them with accompanying code examples.

<a name="feature-entities"></a>
## Feature Entities

The following methods deal with managing feature entities themselves.

<a name="check-feature-existence"></a>
### Check Feature Existence

You may check whether a feature exists in the database, regardless of its active or inactive status.

The `featureExists` method accepts a string or a string-backed enum.

**Returns:** `bool`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$exists = Gatekeeper::featureExists('pumping_tracking');

// or using an enum...

enum Feature: string {
    case PumpingTracking = 'pumping_tracking';
}

$exists = Gatekeeper::featureExists(Feature::PumpingTracking);
```

<a name="create-feature"></a>
### Create Feature

You may create a new feature, which will be active by default.

If the feature already exists, a `FeatureAlreadyExistsException` will be thrown.

The `createFeature` method accepts a string or a string-backed enum.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$feature = Gatekeeper::createFeature('feeding_tracking');

// or using an enum...

enum Feature: string {
    case FeedingTracking = 'feeding_tracking';
}

$feature = Gatekeeper::createFeature(Feature::FeedingTracking);
```

<a name="update-feature"></a>
### Update Feature

You may update the name of an existing feature.

The `updateFeature` method accepts a `FeaturePacket` instance, a string, or a string-backed enum as the first argument (the existing feature), and a string or string-backed enum as the second argument (the new name).

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

If a feature with the new name already exists, a `FeatureAlreadyExistsException` will be thrown.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$updatedFeature = Gatekeeper::updateFeature('feeding_tracking', 'eating_tracking');

// or using enums...

enum Feature: string {
    case FeedingTracking = 'feeding_tracking';
    case EatingTracking = 'eating_tracking';
}

$updatedFeature = Gatekeeper::updateFeature(Feature::FeedingTracking, Feature::EatingTracking);
```

<a name="turn-feature-off-by-default"></a>
### Turn Feature Off by Default

Off by default is the default behavior for features. This means that models may only access a feature if it is directly turned on for them or turned on for a team the model is on.

If a feature is turned on by default, it may be changed to off by default.

The `turnFeatureOffByDefault` method accepts a `FeaturePacket` instance, a string, or a string-backed enum.

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

If the feature is already turned off by default, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$defaultOffFeature = Gatekeeper::turnFeatureOffByDefault('sleeping_tracking');

// or using an enum...

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
}

$defaultOffFeature = Gatekeeper::turnFeatureOffByDefault(Feature::SleepingTracking);
```

<a name="turn-feature-on-by-default"></a>
### Turn Feature On by Default

You may want a feature to be accessible by all models without needing to explicitly assign it.

The `turnFeatureOnByDefault` method accepts a `FeaturePacket` instance, a string, or a string-backed enum.

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

If the feature is already on by default, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$defaultOnFeature = Gatekeeper::turnFeatureOnByDefault('pumping_tracking');

// or using an enum...

enum Feature: string {
    case PumpingTracking = 'pumping_tracking';
}

$defaultOnFeature = Gatekeeper::turnFeatureOnByDefault(Feature::PumpingTracking);
```


<a name="deactivate-feature"></a>
### Deactivate Feature

You may temporarily deactivate a feature if you want it to stop granting access without revoking it from models.

Deactivated features remain in the database but are ignored by feature checks until reactivated. The permissions attached to features will also be ignored until the feature is reactivated.

The `deactivateFeature` method accepts a `FeaturePacket` instance, a string, or a string-backed enum.

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

If the feature is already inactive, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$deactivatedFeature = Gatekeeper::deactivateFeature('sleeping_tracking');

// or using an enum...

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
}

$deactivatedFeature = Gatekeeper::deactivateFeature(Feature::SleepingTracking);
```

<a name="reactivate-feature"></a>
### Reactivate Feature

You may reactivate an inactive feature to resume granting access to models.

The `reactivateFeature` method accepts a `FeaturePacket` instance, a string, or a string-backed enum.

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

If the feature is already active, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$reactivatedFeature = Gatekeeper::reactivateFeature('pumping_tracking');

// or using an enum...

enum Feature: string {
    case PumpingTracking = 'pumping_tracking';
}

$reactivatedFeature = Gatekeeper::reactivateFeature(Feature::PumpingTracking);
```

<a name="delete-feature"></a>
### Delete Feature

You may delete a feature to remove it from your application.

> [!WARNING]
> Deleting a feature will remove it from your application and unassign it from all models.

The `deleteFeature` method accepts a `FeaturePacket` instance, a string, or a string-backed enum.

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

If the feature is already deleted, the method will return `true` without raising an exception.

**Returns:** `bool`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$featureDeleted = Gatekeeper::deleteFeature('growth_tracking');

// or using an enum...

enum Feature: string {
    case GrowthTracking = 'growth_tracking';
}

$featureDeleted = Gatekeeper::deleteFeature(Feature::GrowthTracking);
```

<a name="find-feature-by-name"></a>
### Find Feature by Name

You may retrieve a feature by its name. If the feature does not exist, `null` will be returned.

The `findFeatureByName` method accepts a string or a string-backed enum.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket|null`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$feature = Gatekeeper::findFeatureByName('sleeping_tracking');

// or using an enum...

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
}

$feature = Gatekeeper::findFeatureByName(Feature::SleepingTracking);
```

<a name="get-all-features"></a>
### Get All Features

You may retrieve a collection of all features defined in your application, regardless of their active status.

The `getAllFeatures` method does not take any arguments.

**Returns:** `\Illuminate\Support\Collection<\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$features = Gatekeeper::getAllFeatures();
```

<a name="model-feature-assignments"></a>
## Model Feature Assignments

The following methods allow you to turn on, turn off, and inspect features for models.

> [!NOTE]
> Models passed to Gatekeeper must use the `\Gillyware\Gatekeeper\Traits\HasFeatures` trait to enable the methods described below.

<a name="turn-feature-on-for-model"></a>
### Turn Feature On for Model

You may turn on a feature for a model using one of the following approaches:

- Using the static `Gatekeeper::turnFeatureOnForModel($model, $feature)` method
- Using the fluent `Gatekeeper::for($model)->turnFeatureOn($feature)` chain
- Calling `$model->turnFeatureOn($feature)` directly (available via the `HasFeatures` trait)

The `$feature` argument can be a:

- `FeaturePacket` instance
- string (e.g. `'pumping_tracking'`)
- string-backed enum value

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

**Returns:** `bool` – `true` if the feature was newly turned on or already on

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
}

$user = User::query()->findOrFail(1);

$featureTurnedOn = Gatekeeper::turnFeatureOnForModel($user, Feature::SleepingTracking);

// or fluently...

$featureTurnedOn = Gatekeeper::for($user)->turnFeatureOn(Feature::SleepingTracking);

// or via the trait method...

$featureTurnedOn = $user->turnFeatureOn(Feature::SleepingTracking);
```

<a name="turn-multiple-features-on-for-model"></a>
### Turn Multiple Features On for Model

You may turn on multiple features at once for a model using one of the following approaches:

- Using the static `Gatekeeper::turnAllFeaturesOnForModel($model, $features)` method
- Using the fluent `Gatekeeper::for($model)->turnAllFeaturesOn($features)` chain
- Calling `$model->turnAllFeaturesOn($features)` directly (available via the `HasFeatures` trait)

The `$features` argument must be an array or Arrayable containing any combination of:

- `FeaturePacket` instance
- string (e.g. `'pumping_tracking'`)
- string-backed enum value

If a feature is already on, it will be skipped without raising an exception.

If a feature does not exist, a `FeatureNotFoundException` will be thrown.

> [!NOTE]
> This method stops on the first failure.

**Returns:** `bool` – `true` if all features were successfully turned on or already on

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case FeedingTracking = 'feeding_tracking';
    case EatingTracking = 'eating_tracking';
    case PumpingTracking = 'pumping_tracking';
}

$features = [Feature::FeedingTracking, Feature::EatingTracking, Feature::PumpingTracking];

$user = User::query()->findOrFail(1);

$featuresTurnedOn = Gatekeeper::turnAllFeaturesOnForModel($user, $features);

// or fluently...

$featuresTurnedOn = Gatekeeper::for($user)->turnAllFeaturesOn($features);

// or via the trait method...

$featuresTurnedOn = $user->turnAllFeaturesOn($features);
```

<a name="turn-feature-off-for-model"></a>
### Turn Feature Off for Model

You may turn off a feature for a model using one of the following approaches:

- Using the static `Gatekeeper::turnFeatureOffForModel($model, $feature)` method
- Using the fluent `Gatekeeper::for($model)->turnFeatureOff($feature)` chain
- Calling `$model->turnFeatureOff($feature)` directly (available via the `HasFeatures` trait)

The `$feature` argument can be a:

- `FeaturePacket` instance
- string (e.g. `'pumping_tracking'`)
- string-backed enum value

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

**Returns:** bool – `true` if the feature was turned off or was not previously on

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case PumpingTracking = 'pumping_tracking';
}

$user = User::query()->findOrFail(1);

$featureTurnedOff = Gatekeeper::turnFeatureOffForModel($user, Feature::PumpingTracking);

// or fluently...

$featureTurnedOff = Gatekeeper::for($user)->turnFeatureOff(Feature::PumpingTracking);

// or via the trait method...

$featureTurnedOff = $user->turnFeatureOff(Feature::PumpingTracking);
```

<a name="turn-multiple-features-off-for-model"></a>
### Turn Multiple Features Off for Model

You may turn multiple features off for a model at once using one of the following approaches:

- Using the static `Gatekeeper::turnAllFeaturesOffForModel($model, $features)` method
- Using the fluent `Gatekeeper::for($model)->turnAllFeaturesOff($features)` chain
- Calling `$model->turnAllFeaturesOff($features)` directly (available via the `HasFeatures` trait)

The `$features` argument must be an array or Arrayable containing any combination of:

- `FeaturePacket` instance
- string (e.g. `'pumping_tracking'`)
- string-backed enum value

If a feature is already turned off, it will be skipped without raising an exception.

If a feature does not exist, a `FeatureNotFoundException` will be thrown.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if all features were turned off or were not previously on

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case FeedingTracking = 'feeding_tracking';
    case EatingTracking = 'eating_tracking';
    case PumpingTracking = 'pumping_tracking';
}

$features = [Feature::FeedingTracking, Feature::EatingTracking, Feature::PumpingTracking];

$user = User::query()->findOrFail(1);

$featuresTurnedOff = Gatekeeper::turnAllFeaturesOffForModel($user, $features);

// or fluently...

$featuresTurnedOff = Gatekeeper::for($user)->turnAllFeaturesOff($features);

// or via the trait method...

$featuresTurnedOff = $user->turnAllFeaturesOff($features);
```

<a name="check-model-has-feature"></a>
### Check Model Has Feature

A model may have an active feature directly turned on, indirectly on through a team, or on by default. This method checks all sources to determine if the model has access to the feature.

You may check if a model has a feature using one of the following approaches:

- Using the static `Gatekeeper::modelHasFeature($model, $feature)` method
- Using the fluent `Gatekeeper::for($model)->hasFeature($feature)` chain
- Calling `$model->hasFeature($feature)` directly (available via the `HasFeatures` trait)

The `$feature` argument can be a:

- `FeaturePacket` instance
- string (e.g. `'pumping_tracking'`)
- string-backed enum value

If the feature does not exist, `false` will be returned.

**Returns:** bool – `true` if the model has access to the given feature, `false` otherwise (including if the model is missing the `HasFeatures` trait, the feature does not exist, or the feature is inactive)

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case PumpingTracking = 'pumping_tracking';
}

$user = User::query()->findOrFail(1);

$hasFeature = Gatekeeper::modelHasFeature($user, Feature::PumpingTracking);

// or fluently...

$hasFeature = Gatekeeper::for($user)->hasFeature(Feature::PumpingTracking);

// or via the trait method...

$hasFeature = $user->hasFeature(Feature::PumpingTracking);
```

<a name="check-model-has-any-feature"></a>
### Check Model Has Any Feature

A model may have an active feature directly turned on, indirectly on through a team, or on by default. This method checks all sources to determine if the model has access to any of the given features.

You may check if a model has any of a set of features using one of the following approaches:

- Using the static `Gatekeeper::modelHasAnyFeature($model, $features)` method
- Using the fluent `Gatekeeper::for($model)->hasAnyFeature($features)` chain
- Calling `$model->hasAnyFeature($features)` directly (available via the `HasFeatures` trait)

The `$features` argument must be an array or Arrayable containing any combination of:

- `FeaturePacket` instance
- string (e.g. `'pumping_tracking'`)
- string-backed enum value

If the feature does not exist, it will be skipped.

**Returns:** bool – `true` if the model has access to any of the given features, `false` otherwise (including if none of the features exist, are active, or are assigned)

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case FeedingTracking = 'feeding_tracking';
    case EatingTracking = 'eating_tracking';
    case PumpingTracking = 'pumping_tracking';
}

$features = [Feature::FeedingTracking, Feature::EatingTracking, Feature::PumpingTracking];

$user = User::query()->findOrFail(1);

$hasAnyFeature = Gatekeeper::modelHasAnyFeature($user, $features);

// or fluently...

$hasAnyFeature = Gatekeeper::for($user)->hasAnyFeature($features);

// or via the trait method...

$hasAnyFeature = $user->hasAnyFeature($features);
```

<a name="check-model-has-all-features"></a>
### Check Model Has All Features

A model may have an active feature directly turned on, indirectly on through a team, or on by default. This method checks all sources to determine if the model has access to all of the given features.

You may check if a model has all of a set of features using one of the following approaches:

- Using the static `Gatekeeper::modelHasAllFeatures($model, $features)` method
- Using the fluent `Gatekeeper::for($model)->hasAllFeatures($features)` chain
- Calling `$model->hasAllFeatures($features)` directly (available via the `HasFeatures` trait)

The `$features` argument must be an array or Arrayable containing any combination of:

- `FeaturePacket` instance
- string (e.g. `'pumping_tracking'`)
- string-backed enum value

If the feature does not exist, `false` will be returned.

**Returns:** bool – `true` if the model has access to all of the given features, `false` otherwise (including if any of the features do not exist, are inactive, or are unassigned)

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case FeedingTracking = 'feeding_tracking';
    case EatingTracking = 'eating_tracking';
    case PumpingTracking = 'pumping_tracking';
}

$features = [Feature::FeedingTracking, Feature::EatingTracking, Feature::PumpingTracking];

$user = User::query()->findOrFail(1);

$hasAllFeatures = Gatekeeper::modelHasAllFeatures($user, $features);

// or fluently...

$hasAllFeatures = Gatekeeper::for($user)->hasAllFeatures($features);

// or via the trait method...

$hasAllFeatures = $user->hasAllFeatures($features);
```

<a name="get-direct-features-for-model"></a>
### Get Direct Features for Model

You may retrieve a collection of all features directly turned on for a given model, regardless of their active status. This does not include features inherited from teams or those features that are on by default.

You may get a model's direct features using one of the following approaches:

- Using the static `Gatekeeper::getDirectFeaturesForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getDirectFeatures()` chain
- Calling `$model->getDirectFeatures()` directly (available via the `HasFeatures` trait)

**Returns:** `\Illuminate\Support\Collection<\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$directFeatures = Gatekeeper::getDirectFeaturesForModel($user);

// or fluently...

$directFeatures = Gatekeeper::for($user)->getDirectFeatures();

// or via the trait method...

$directFeatures = $user->getDirectFeatures();
```

<a name="get-effective-features-for-model"></a>
### Get Effective Features for Model

You may retrieve a collection of all active features effectively on for a given model, including those inherited from teams and those that are on by default.

You may get a model's effective features using one of the following approaches:

- Using the static `Gatekeeper::getEffectiveFeaturesForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getEffectiveFeatures()` chain
- Calling `$model->getEffectiveFeatures()` directly (available via the `HasFeatures` trait)

**Returns:** `\Illuminate\Support\Collection<\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$effectiveFeatures = Gatekeeper::getEffectiveFeaturesForModel($user);

// or fluently...

$effectiveFeatures = Gatekeeper::for($user)->getEffectiveFeatures();

// or via the trait method...

$effectiveFeatures = $user->getEffectiveFeatures();
```

<a name="get-verbose-features-for-model"></a>
### Get Verbose Features for Model

You may retrieve a collection of all active features effectively assigned to a given model, along with the source(s) of each feature (e.g., direct, via team, or on by default).

You may get a model's verbose features using one of the following approaches:

- Using the static `Gatekeeper::getVerboseFeaturesForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getVerboseFeatures()` chain
- Calling `$model->getVerboseFeatures()` directly (available via the `HasFeatures` trait)

**Returns:** `\Illuminate\Support\Collection<array{name: string, sources: array}>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$user = User::query()->findOrFail(1);

$verboseFeatures = Gatekeeper::getVerboseFeaturesForModel($user);

// or fluently...

$verboseFeatures = Gatekeeper::for($user)->getVerboseFeatures();

// or via the trait method...

$verboseFeatures = $user->getVerboseFeatures();
```

<a name="next-steps"></a>
## Next Steps

Entities:
- [Permissions](permissions.md)
- [Roles](roles.md)
- [Teams](teams.md)

Control Access with Entities:
- [Middleware](middleware.md)
- [Blade Directives](blade-directives.md)

Manage Entities and Assignments:
- [Artisan Commands](artisan-commands.md)

Track Entity and Entity Assignment Changes:
- [Audit Logging]('audit-logging.md')
