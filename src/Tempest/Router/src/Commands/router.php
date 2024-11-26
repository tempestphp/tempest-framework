<?php

declare(strict_types=1);

$publicPath = $_SERVER['DOCUMENT_ROOT'];

if (file_exists($publicPath . $_SERVER['REQUEST_URI'])) {
    return false;
}

require_once $publicPath . '/index.php';
