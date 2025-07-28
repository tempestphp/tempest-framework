<?php
/**
 * @var string|null $title The webpage's title
 */
?>

<html lang="en" class="h-dvh flex flex-col scroll-smooth">
<head>
    <title>{{ $title ?? 'Tempest' }}</title>

    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <x-slot name="head"/>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="flex flex-col h-full antialiased">
<x-slot/>
<x-slot name="scripts"/>
</body>
</html>
