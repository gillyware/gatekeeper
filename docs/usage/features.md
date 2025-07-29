# Features

- [Feature Entities](#feature-entities)
    - [Check Feature Existence](#check-feature-existence)
    - [Create Feature](#create-feature)
    - [Update Feature Name](#update-feature-name)
    - [Grant Feature by Default](#grant-feature-by-default)
    - [Revoke Feature Default Grant](#revoke-feature-default-grant)
    - [Deactivate Feature](#deactivate-feature)
    - [Reactivate Feature](#reactivate-feature)
    - [Delete Feature](#delete-feature)
    - [Find Feature by Name](#find-feature-by-name)
    - [Get All Features](#get-all-features)
- [Model Feature Relationships](#model-feature-relationships)
    - [Assign Feature to Model](#assign-feature-to-model)
    - [Assign Multiple Features to Model](#assign-multiple-features-to-model)
    - [Unassign Feature from Model](#unassign-feature-from-model)
    - [Unassign Multiple Features from Model](#unassign-multiple-features-from-model)
    - [Deny Feature from Model](#deny-feature-from-model)
    - [Deny Multiple Features from Model](#deny-multiple-features-from-model)
    - [Undeny Feature from Model](#undeny-feature-from-model)
    - [Undeny Multiple Features from Model](#undeny-multiple-features-from-model)
    - [Check Model Has Feature](#check-model-has-feature)
    - [Check Model Has Any Feature](#check-model-has-any-feature)
    - [Check Model Has All Features](#check-model-has-all-features)
    - [Get Direct Features for Model](#get-direct-features-for-model)
    - [Get Effective Features for Model](#get-effective-features-for-model)
    - [Get Verbose Features for Model](#get-verbose-features-for-model)
- [Next Steps](#next-steps)

A feature is a named grouping of permissions. You can assign features directly to any (configured) model and any team.

By default, created features are active and not granted by default. 'Active' means the feature is actively granting access, and 'not granted by default' means a feature must be explicitly assigned to models, either directly or through another entity directly assigned to the model.

A model’s effective features are the union of its features granted by default, direct features, and those inherited through its teams, excluding the features directly denied from the model. Keep in mind, a model will effectively have no features (granted by default, direct, or inherited) if the model is not using the `HasFeatures` trait.

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
    case SleepingTracking = 'feeding_tracking';
}

$feature = Gatekeeper::createFeature(Feature::SleepingTracking);
```

<a name="update-feature-name"></a>
### Update Feature Name

You may update the name of an existing feature.

The `updateFeatureName` method accepts a `FeaturePacket` instance, a string, or a string-backed enum as the first argument (the existing feature), and a string or string-backed enum as the second argument (the new name).

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

If a feature with the new name already exists, a `FeatureAlreadyExistsException` will be thrown.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$updatedFeature = Gatekeeper::updateFeatureName('feeding_tracking', 'eating_tracking');

// or using enums...

enum Feature: string {
    case SleepingTracking = 'feeding_tracking';
    case EatingTracking = 'eating_tracking';
}

$updatedFeature = Gatekeeper::updateFeatureName(Feature::SleepingTracking, Feature::EatingTracking);
```

<a name="grant-feature-by-default"></a>
### Grant Feature by Default

You may want a feature that most, if not all, models should have by default. Granting the feature by default effectively assigns it to all models that are not denying it.

The `grantFeatureByDefault` method accepts a `FeaturePacket` instance, a string, or a string-backed enum.

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

If the feature is already granted by default, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$grantedByDefaultFeature = Gatekeeper::grantFeatureByDefault('sleeping_tracking');

// or using an enum...

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
}

$grantedByDefaultFeature = Gatekeeper::grantFeatureByDefault(Feature::SleepingTracking);
```

<a name="revoke-feature-default-grant"></a>
### Revoke Feature Default Grant

You may decide that a feature should not be [granted by default](#grant-feature-by-default). You can easily revoke a feature's default grant.

The `revokeFeatureDefaultGrant` method accepts a `FeaturePacket` instance, a string, or a string-backed enum.

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

If the feature is not granted by default, it will simply be returned without raising an exception.

**Returns:** `\Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$nonGrantedByDefaultFeature = Gatekeeper::revokeFeatureDefaultGrant('sleeping_tracking');

// or using an enum...

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
}

$nonGrantedByDefaultFeature = Gatekeeper::revokeFeatureDefaultGrant(Feature::SleepingTracking);
```

<a name="deactivate-feature"></a>
### Deactivate Feature

You may temporarily deactivate a feature if you want it to stop granting access without unassigning it from models.

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

**Returns:** `\Illuminate\Support\Collection<string, \Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket>`

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

$features = Gatekeeper::getAllFeatures();
```

<a name="model-feature-relationships"></a>
## Model Feature Relationships

The following methods allow you to assign, unassign, deny, undeny, and inspect features for models.

> [!NOTE]
> Models passed to Gatekeeper must use the `\Gillyware\Gatekeeper\Traits\HasFeatures` trait to enable the methods described below.

<a name="assign-feature-to-model"></a>
### Assign Feature to Model

You may assign a feature to a model using one of the following approaches:

- Using the static `Gatekeeper::assignFeatureToModel($model, $feature)` method
- Using the fluent `Gatekeeper::for($model)->assignFeature($feature)` chain
- Calling `$model->assignFeature($feature)` directly (available via the `HasFeatures` trait)

The `$feature` argument must be a `FeaturePacket` instance, a string, or a string-backed enum.

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

If the feature is denied from a model, the denial will be removed before assigning.

**Returns:** `bool` – `true` if the feature is assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
}

$user = User::query()->findOrFail(1);

$featureAssigned = Gatekeeper::assignFeatureToModel($user, Feature::SleepingTracking);

// or fluently...

$featureAssigned = Gatekeeper::for($user)->assignFeature(Feature::SleepingTracking);

// or via the trait method...

$featureAssigned = $user->assignFeature(Feature::SleepingTracking);
```

<a name="assign-multiple-features-to-model"></a>
### Assign Multiple Features to Model

You may assign multiple features to a model using one of the following approaches:

- Using the static `Gatekeeper::assignAllFeaturesToModel($model, $features)` method
- Using the fluent `Gatekeeper::for($model)->assignAllFeatures($features)` chain
- Calling `$model->assignAllFeatures($features)` directly (available via the `HasFeatures` trait)

The `$features` argument must be an array or Arrayable containing any combination `FeaturePacket` instances, strings, or a string-backed enums.

If a feature is already assigned, it will be skipped without raising an exception.

If a feature does not exist, a `FeatureNotFoundException` will be thrown.

If a feature is denied from a model, the denial will be removed before assigning.

> [!NOTE]
> This method stops on the first failure.

**Returns:** `bool` – `true` if all the features are assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
    case EatingTracking = 'eating_tracking';
    case PumpingTracking = 'pumping_tracking';
}

$features = [Feature::SleepingTracking, Feature::EatingTracking, Feature::PumpingTracking];

$user = User::query()->findOrFail(1);

$featuresAssigned = Gatekeeper::assignAllFeaturesToModel($user, $features);

// or fluently...

$featuresAssigned = Gatekeeper::for($user)->assignAllFeatures($features);

// or via the trait method...

$featuresAssigned = $user->assignAllFeatures($features);
```

<a name="unassign-feature-from-model"></a>
### Unassign Feature from Model

You may unassign a feature from a model using one of the following approaches:

- Using the static `Gatekeeper::unassignFeatureFromModel($model, $feature)` method
- Using the fluent `Gatekeeper::for($model)->unassignFeature($feature)` chain
- Calling `$model->unassignFeature($feature)` directly (available via the `HasFeatures` trait)

The `$feature` argument must be a `FeaturePacket` instance, a string, or a string-backed enum.

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

If the feature is denied from the model, the denial will remain intact.

**Returns:** bool – `true` if the feature is not assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case PumpingTracking = 'pumping_tracking';
}

$user = User::query()->findOrFail(1);

$featureUnassigned = Gatekeeper::unassignFeatureFromModel($user, Feature::PumpingTracking);

// or fluently...

$featureUnassigned = Gatekeeper::for($user)->unassignFeature(Feature::PumpingTracking);

// or via the trait method...

$featureUnassigned = $user->unassignFeature(Feature::PumpingTracking);
```

<a name="unassign-multiple-features-from-model"></a>
### Unassign Multiple Features from Model

You may unassign multiple features from a model using one of the following approaches:

- Using the static `Gatekeeper::unassignAllFeaturesFromModel($model, $features)` method
- Using the fluent `Gatekeeper::for($model)->unassignAllFeatures($features)` chain
- Calling `$model->unassignAllFeatures($features)` directly (available via the `HasFeatures` trait)

The `$features` argument must be an array or Arrayable containing any combination `FeaturePacket` instances, strings, or a string-backed enums.

If a feature is already unassigned, it will be skipped without raising an exception.

If a feature does not exist, a `FeatureNotFoundException` will be thrown.

If the feature is denied from the model, the denial will remain intact.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if none of the features are assigned

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
    case EatingTracking = 'eating_tracking';
    case PumpingTracking = 'pumping_tracking';
}

$features = [Feature::SleepingTracking, Feature::EatingTracking, Feature::PumpingTracking];

$user = User::query()->findOrFail(1);

$featuresUnassigned = Gatekeeper::unassignAllFeaturesFromModel($user, $features);

// or fluently...

$featuresUnassigned = Gatekeeper::for($user)->unassignAllFeatures($features);

// or via the trait method...

$featuresUnassigned = $user->unassignAllFeatures($features);
```

<a name="deny-feature-from-model"></a>
### Deny Feature from Model

To deny a feature from a model means to block access to a feature even if the feature is granted by default or inherited from a team.

You may deny a feature from a model using one of the following approaches:

- Using the static `Gatekeeper::denyFeatureFromModel($model, $feature)` method
- Using the fluent `Gatekeeper::for($model)->denyFeature($feature)` chain
- Calling `$model->denyFeature($feature)` directly (available via the `HasFeatures` trait)

The `$feature` argument must be a `FeaturePacket` instance, a string, or a string-backed enum.

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

If the feature is assigned to the model, the feature will be unassigned from the model before denying.

**Returns:** bool – `true` if the feature is denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case PumpingTracking = 'pumping_tracking';
}

$user = User::query()->findOrFail(1);

$featureDenied = Gatekeeper::denyFeatureFromModel($user, Feature::PumpingTracking);

// or fluently...

$featureDenied = Gatekeeper::for($user)->denyFeature(Feature::PumpingTracking);

// or via the trait method...

$featureDenied = $user->denyFeature(Feature::PumpingTracking);
```

<a name="deny-multiple-features-from-model"></a>
### Deny Multiple Features from Model

You may deny multiple features from a model using one of the following approaches:

- Using the static `Gatekeeper::denyAllFeaturesFromModel($model, $features)` method
- Using the fluent `Gatekeeper::for($model)->denyAllFeatures($features)` chain
- Calling `$model->denyAllFeatures($features)` directly (available via the `HasFeatures` trait)

The `$features` argument must be an array or Arrayable containing any combination `FeaturePacket` instances, strings, or a string-backed enums.

If a feature is already denied, it will be skipped without raising an exception.

If a feature does not exist, a `FeatureNotFoundException` will be thrown.

If the feature is denied from the model, the denial will remain intact.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if all features are denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
    case EatingTracking = 'eating_tracking';
    case PumpingTracking = 'pumping_tracking';
}

$features = [Feature::SleepingTracking, Feature::EatingTracking, Feature::PumpingTracking];

$user = User::query()->findOrFail(1);

$featuresDenied = Gatekeeper::denyAllFeaturesFromModel($user, $features);

// or fluently...

$featuresDenied = Gatekeeper::for($user)->denyAllFeatures($features);

// or via the trait method...

$featuresDenied = $user->denyAllFeatures($features);
```

<a name="undeny-feature-from-model"></a>
### Undeny Feature from Model

To undeny a feature from a model means to unblock access to a feature, allowing acces if the feature is granted by default, directly assigned, or inherited from a team.

You may undeny a feature from a model using one of the following approaches:

- Using the static `Gatekeeper::undenyFeatureFromModel($model, $feature)` method
- Using the fluent `Gatekeeper::for($model)->undenyFeature($feature)` chain
- Calling `$model->undenyFeature($feature)` directly (available via the `HasFeatures` trait)

The `$feature` argument must be a `FeaturePacket` instance, a string, or a string-backed enum.

If the feature does not exist, a `FeatureNotFoundException` will be thrown.

**Returns:** bool – `true` if the feature is not denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case PumpingTracking = 'pumping_tracking';
}

$user = User::query()->findOrFail(1);

$featureUndenied = Gatekeeper::undenyFeatureFromModel($user, Feature::PumpingTracking);

// or fluently...

$featureUndenied = Gatekeeper::for($user)->undenyFeature(Feature::PumpingTracking);

// or via the trait method...

$featureUndenied = $user->undenyFeature(Feature::PumpingTracking);
```

<a name="undeny-multiple-features-from-model"></a>
### Undeny Multiple Features from Model

You may undeny multiple features from a model using one of the following approaches:

- Using the static `Gatekeeper::undenyAllFeaturesFromModel($model, $features)` method
- Using the fluent `Gatekeeper::for($model)->undenyAllFeatures($features)` chain
- Calling `$model->undenyAllFeatures($features)` directly (available via the `HasFeatures` trait)

The `$features` argument must be an array or Arrayable containing any combination `FeaturePacket` instances, strings, or a string-backed enums.

If a feature is not denied, it will be skipped without raising an exception.

If a feature does not exist, a `FeatureNotFoundException` will be thrown.

> [!NOTE]
> This method stops on the first failure.

**Returns:** bool – `true` if none of the features are denied

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
    case EatingTracking = 'eating_tracking';
    case PumpingTracking = 'pumping_tracking';
}

$features = [Feature::SleepingTracking, Feature::EatingTracking, Feature::PumpingTracking];

$user = User::query()->findOrFail(1);

$featuresUndenied = Gatekeeper::undenyAllFeaturesFromModel($user, $features);

// or fluently...

$featuresUndenied = Gatekeeper::for($user)->undenyAllFeatures($features);

// or via the trait method...

$featuresUndenied = $user->undenyAllFeatures($features);
```

<a name="check-model-has-feature"></a>
### Check Model Has Feature

A model may have an undenied, active feature granted by default, directly assigned, or indirectly assigned through a team. This method checks all sources to determine if the model has access to the feature.

You may check if a model has a feature using one of the following approaches:

- Using the static `Gatekeeper::modelHasFeature($model, $feature)` method
- Using the fluent `Gatekeeper::for($model)->hasFeature($feature)` chain
- Calling `$model->hasFeature($feature)` directly (available via the `HasFeatures` trait)

The `$feature` argument must be a `FeaturePacket` instance, a string, or a string-backed enum.

If the feature does not exist, `false` will be returned.

**Returns:** bool – `true` if the model has access to the given feature

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

A model may have an undenied, active feature granted by default, directly assigned, or indirectly assigned through a team. This method checks all sources to determine if the model has access to any of the given features.

You may check if a model has any of a set of features using one of the following approaches:

- Using the static `Gatekeeper::modelHasAnyFeature($model, $features)` method
- Using the fluent `Gatekeeper::for($model)->hasAnyFeature($features)` chain
- Calling `$model->hasAnyFeature($features)` directly (available via the `HasFeatures` trait)

The `$features` argument must be an array or Arrayable containing any combination `FeaturePacket` instances, strings, or a string-backed enums.

If the feature does not exist, it will be skipped.

**Returns:** bool – `true` if the model has access to any of the given features

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
    case EatingTracking = 'eating_tracking';
    case PumpingTracking = 'pumping_tracking';
}

$features = [Feature::SleepingTracking, Feature::EatingTracking, Feature::PumpingTracking];

$user = User::query()->findOrFail(1);

$hasAnyFeature = Gatekeeper::modelHasAnyFeature($user, $features);

// or fluently...

$hasAnyFeature = Gatekeeper::for($user)->hasAnyFeature($features);

// or via the trait method...

$hasAnyFeature = $user->hasAnyFeature($features);
```

<a name="check-model-has-all-features"></a>
### Check Model Has All Features

A model may have an undenied, active feature granted by default, directly assigned, or indirectly assigned through a team. This method checks all sources to determine if the model has access to all of the given features.

You may check if a model has all of a set of features using one of the following approaches:

- Using the static `Gatekeeper::modelHasAllFeatures($model, $features)` method
- Using the fluent `Gatekeeper::for($model)->hasAllFeatures($features)` chain
- Calling `$model->hasAllFeatures($features)` directly (available via the `HasFeatures` trait)

The `$features` argument must be an array or Arrayable containing any combination `FeaturePacket` instances, strings, or a string-backed enums.

If the feature does not exist, `false` will be returned.

**Returns:** bool – `true` if the model has access to all of the given features

```php
use Gillyware\Gatekeeper\Facades\Gatekeeper;

enum Feature: string {
    case SleepingTracking = 'sleeping_tracking';
    case EatingTracking = 'eating_tracking';
    case PumpingTracking = 'pumping_tracking';
}

$features = [Feature::SleepingTracking, Feature::EatingTracking, Feature::PumpingTracking];

$user = User::query()->findOrFail(1);

$hasAllFeatures = Gatekeeper::modelHasAllFeatures($user, $features);

// or fluently...

$hasAllFeatures = Gatekeeper::for($user)->hasAllFeatures($features);

// or via the trait method...

$hasAllFeatures = $user->hasAllFeatures($features);
```

<a name="get-direct-features-for-model"></a>
### Get Direct Features for Model

You may retrieve a collection of all features directly assigned to a given model, regardless of their active status. This does not include features inherited from teams.

You may get a model's direct features using one of the following approaches:

- Using the static `Gatekeeper::getDirectFeaturesForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getDirectFeatures()` chain
- Calling `$model->getDirectFeatures()` directly (available via the `HasFeatures` trait)

**Returns:** `\Illuminate\Support\Collection<string, \Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket>`

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

You may retrieve a collection of all active features effectively assigned to a given model, including those inherited from teams.

You may get a model's effective features using one of the following approaches:

- Using the static `Gatekeeper::getEffectiveFeaturesForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getEffectiveFeatures()` chain
- Calling `$model->getEffectiveFeatures()` directly (available via the `HasFeatures` trait)

**Returns:** `\Illuminate\Support\Collection<string, \Gillyware\Gatekeeper\Packets\Entities\Feature\FeaturePacket>`

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

You may retrieve a collection of all active features effectively assigned to a given model, along with the source(s) of each feature (ex., direct or via team).

You may get a model's verbose features using one of the following approaches:

- Using the static `Gatekeeper::getVerboseFeaturesForModel($model)` method
- Using the fluent `Gatekeeper::for($model)->getVerboseFeatures()` chain
- Calling `$model->getVerboseFeatures()` directly (available via the `HasFeatures` trait)

**Returns:** `\Illuminate\Support\Collection`

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
- [Audit Logging](audit-logging.md)
