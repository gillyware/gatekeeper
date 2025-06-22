<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Gatekeeper{{ config('app.name') ? ' – ' . config('app.name') : '' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600" rel="stylesheet" />

    {{ Gillyware\Gatekeeper\Dashboard\Gatekeeper::css() }}
    {{ Gillyware\Gatekeeper\Dashboard\Gatekeeper::js() }}
</head>
<body class="bg-white text-gray-900 dark:bg-gray-900 dark:text-white">
    <div id="gatekeeper-root"></div>
</body>
</html>
