<?php

use Tempest\Auth\AuthController;
use function Tempest\uri;

?>
<html lang="en">
<head>
    <title>Login</title>
</head>
<body>

<x-form :if="$this->user === null" action="<?= uri([AuthController::class, 'attemptLogin']) ?>">
    <x-input name="email" type="email" label="Email"/>
    <x-input name="password" type="password" label="Password" />

    <x-submit label="Login" />
</x-form>

<x-form :else action="<?= uri([AuthController::class, 'logout']) ?>">
    <x-submit label="Logout" />
</x-form>

</body>
</html>