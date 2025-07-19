# Installation

- [Install Gatekeeper](#install-gatekeeper)
- [Setup Database](#set-up-database)
- [Prepare Your User Model](#prepare-your-user-model)
- [Assign Permissions](#assign-permissions)
- [Access the Dashboard](#access-the-dashboard)
- [Next Steps](#next-steps)

<a name="install-gatekeeper"></a>
## Install Gatekeeper

You may install Gatekeeper into your project using the Composer package manager:

```shell
composer require gillyware/gatekeeper
```

After installing Gatekeeper, publish its configuration file and migrations:

```shell
php artisan vendor:publish --provider="Gillyware\Gatekeeper\GatekeeperServiceProvider"
```

<a name="set-up-database"></a>
## Set Up Database

Run the migrations that were added to your project:

```shell
php artisan migrate
```

Then seed the permissions required for the Gatekeeper dashboard:

```shell
php artisan db:seed --class="Gillyware\Gatekeeper\Database\Seeders\GatekeeperPermissionsSeeder"
```

<a name="prepare-your-user-model"></a>
## Prepare Your User Model

Uncomment the `User` model (under `models.manageable`) in the added `gatekeeper.php` configuration file.

> [!NOTE]
> This is where all models that will have permissions, roles, or team memberships should be registered. This is how the dashboard and Artisan commands know which models to manage, search, and display. Ensure the models are correctly configured according to the [configuration specs](./docs/configuration.md#models).

Add the `\Gillyware\Gatekeeper\Traits\HasPermissions` trait to your user model. This is required for Gatekeeper to permit access to (and allow assignments of) permissions.

<a name="assign-permissions"></a>
## Assign Permissions

Now the necessary permissions exist, and your users can take permissions, so it's time to use an Artisan command to assign these permissions to the user(s) you would like to access the dashboard.

Start by opening the interactive Artisan permission tool:

```shell
php artisan gatekeeper:permission
```

Choose "Assign one or more permissions to a model", then select:
 - `gatekeeper.view` - to access the dashboard
 - `gatekeeper.manage` - to manage permissions, roles, teams, and their assignments

Then, select the User model and search for your target user using any of the searchable columns specified in your configuration.

> [!NOTE]
> If audit logging is enabled, you will be prompted to attribute this action to a model. You may attribute the action to the yourself or to the system.

<a name="access-the-dashboard"></a>
## Access the Dashboard

That's it! You can now access the Gatekeeper dashboard at:

```shell
{APP_URL}/gatekeeper
```

<a name="next-steps"></a>
## Next Steps

Now that Gatekeeper is installed and ready, you may customize its behavior by updating the configuration file:

[Configuration](configuration.md)
