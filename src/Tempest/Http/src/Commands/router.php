<?php

declare(strict_types=1);

$publicPath = getcwd() . '/' . rtrim($_ENV['TEMPEST_PUBLIC_DIR'], '/');

if (file_exists($publicPath . $_SERVER['REQUEST_URI'])) {
    return false;
}

require_once $publicPath . '/index.php';
