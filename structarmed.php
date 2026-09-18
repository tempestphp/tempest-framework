<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->layer('Auth', 'packages/auth/src')
    ->layer('Cache', 'packages/cache/src')
    ->layer('Clock', 'packages/clock/src')
    ->layer('CommandBus', 'packages/command-bus/src')
    ->layer('Console', 'packages/console/src')
    ->layer('Container', 'packages/container/src')
    ->layer('Core', 'packages/core/src')
    ->layer('Cryptography', 'packages/cryptography/src')
    ->layer('Database', 'packages/database/src')
    ->layer('DateTime', 'packages/datetime/src')
    ->layer('Debug', 'packages/debug/src')
    ->layer('Discovery', 'packages/discovery/src')
    ->layer('EventBus', 'packages/event-bus/src')
    ->layer('Framework', 'src/Tempest/Framework')
    ->layer('Generation', 'packages/generation/src')
    ->layer('Http', 'packages/http/src')
    ->layer('HttpClient', 'packages/http-client/src')
    ->layer('Icon', 'packages/icon/src')
    ->layer('Idempotency', 'packages/idempotency/src')
    ->layer('Intl', 'packages/intl/src')
    ->layer('KeyValue', 'packages/kv-store/src')
    ->layer('Log', 'packages/log/src')
    ->layer('Mail', 'packages/mail/src')
    ->layer('Mapper', 'packages/mapper/src')
    ->layer('Mcp', 'packages/mcp/src')
    ->layer('Process', 'packages/process/src')
    ->layer('Reflection', 'packages/reflection/src')
    ->layer('Router', 'packages/router/src')
    ->layer('Storage', 'packages/storage/src')
    ->layer('Support', 'packages/support/src')
    ->layer('Upgrade', 'packages/upgrade/src')
    ->layer('Validation', 'packages/validation/src')
    ->layer('View', 'packages/view/src')
    ->layer('Vite', 'packages/vite/src')
    ->ruleset([
        'Auth'         => ['Container', 'Discovery', 'Http', 'Mapper', 'Reflection', 'Router', 'Support'],
        'Cache'        => ['+Clock', 'Console', 'Core', 'Discovery', 'Icon', 'KeyValue', 'Reflection', 'View'],
        'Clock'        => ['Container', 'DateTime'],
        'CommandBus'   => ['+Clock', '+Container', 'Database', '+Generation', 'KeyValue'],
        'Console'      => ['CommandBus', '+Discovery', '+EventBus', 'Http', 'Router'],
        'Container'    => ['Console', 'Core', 'Discovery', 'Generation', 'Reflection', 'Support', 'Validation'],
        'Core'         => ['+Discovery', '+Generation', '+Log', 'Router'],
        'Cryptography' => ['+Clock', 'Console', 'Core'],
        'Database'     => ['+Container', 'Cryptography', '+EventBus', '+Generation', '+Mapper', 'Router'],
        'DateTime'     => ['+Clock', 'Intl', 'Support'],
        'Debug'        => ['Console', 'Container', 'EventBus'],
        'Discovery'    => ['+Container', 'Process'],
        'EventBus'     => ['Container', 'Core', 'Discovery', 'Reflection'],
        'Framework'    => ['+Auth', '+Cryptography', '+Database', '+Discovery', '+Mail', '+Mcp', '+View', '+Vite'],
        'Generation'   => ['Console', 'Container', 'DateTime', 'Discovery', 'Reflection', 'Support'],
        'Http'         => ['+Cryptography', '+Database', '+KeyValue', 'View'],
        'HttpClient'   => ['Container', 'Http'],
        'Icon'         => ['DateTime', 'EventBus', '+HttpClient', 'Support'],
        'Idempotency'  => ['Cache', 'CommandBus', 'Container', 'DateTime', 'Discovery', 'Http', 'Reflection', 'Router', 'Support', 'View'],
        'Intl'         => ['DateTime', '+EventBus', 'Icon'],
        'KeyValue'     => ['Container', 'Core', 'DateTime', 'EventBus', 'Support'],
        'Log'          => ['Console', 'Container', 'Core', 'EventBus', 'Reflection'],
        'Mail'         => ['Container', 'DateTime', 'EventBus', 'Mapper', 'Storage', 'View'],
        'Mapper'       => ['Container', 'DateTime', 'Discovery', 'Reflection', 'Support', 'Validation'],
        'Mcp'          => ['Console', 'Container', 'Core', 'Discovery', 'Http', 'Mapper', 'Reflection', 'Router', 'Validation'],
        'Process'      => ['Container', 'Core', 'DateTime', 'Support'],
        'Reflection'   => [],
        'Router'       => ['Auth', 'Cryptography', '+EventBus', '+Generation', '+HttpClient', '+Support', '+Validation', 'View', '+Vite'],
        'Storage'      => ['Container', 'Discovery', 'Reflection', 'Support'],
        'Support'      => ['Console', 'Debug', 'Intl', 'Validation'],
        'Upgrade'      => ['Core', 'Database', 'Router'],
        'Validation'   => ['Container', 'Core', 'DateTime', 'Intl', 'Reflection', 'Support'],
        'View'         => ['Cache', '+Container'],
        'Vite'         => ['+Container'],
    ])
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->skip([
        'packages/auth/src/Installer',
        'packages/**/tests',
        __DIR__ . '/tests',
    ]);
