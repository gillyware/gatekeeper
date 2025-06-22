<?php

namespace Gillyware\Gatekeeper\Dashboard;

use Gillyware\Gatekeeper\Constants\GatekeeperPermissionName;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;
use RuntimeException;

class Gatekeeper
{
    /**
     * Get the CSS for the Gatekeeper dashboard.
     */
    public static function css(): Htmlable
    {
        if (($app = @file_get_contents(__DIR__.'/../../dist/app.css')) === false) {
            throw new RuntimeException('Unable to load the Gatekeeper dashboard CSS.');
        }

        return new HtmlString(<<<HTML
            <style>{$app}</style>
            HTML);
    }

    /**
     * Get the JS for the Gatekeeper dashboard.
     */
    public static function js(): Htmlable
    {
        if (($js = @file_get_contents(__DIR__.'/../../dist/app.js')) === false) {
            throw new RuntimeException('Unable to load the Gatekeeper dashboard JavaScript.');
        }

        $gatekeeper = Js::from(static::scriptVariables());

        return new HtmlString(<<<HTML
            <script type="module">
                window.Gatekeeper = {$gatekeeper};
                {$js}
            </script>
            HTML);
    }

    /**
     * Get the default JavaScript variables for Gatekeeper.
     */
    public static function scriptVariables(): array
    {
        $user = auth()->user();

        return [
            'config' => [
                'path' => Config::get('gatekeeper.path'),
                'audit_enabled' => Config::get('gatekeeper.features.audit'),
                'roles_enabled' => Config::get('gatekeeper.features.roles'),
                'teams_enabled' => Config::get('gatekeeper.features.teams'),
            ],
            'user' => [
                'name' => (string) $user?->name,
                'email' => (string) $user?->email,
                'permissions' => [
                    'can_view' => (bool) $user?->hasPermission(GatekeeperPermissionName::VIEW),
                    'can_manage' => (bool) $user?->hasPermission(GatekeeperPermissionName::MANAGE),
                ],
            ],
        ];
    }
}
