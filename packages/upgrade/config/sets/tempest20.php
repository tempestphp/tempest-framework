<?php

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\RenameProperty;
use Tempest\Upgrade\Tempest2\MigrationRector;
use Tempest\Upgrade\Tempest2\RemoveDatabaseMigrationImportRector;
use Tempest\Upgrade\Tempest2\RemoveIdImportRector;
use Tempest\Upgrade\Tempest2\UpdateUriImportsRector;

return static function (RectorConfig $config): void {
    $config->importNames();
    $config->importShortClasses();

    $config->rule(MigrationRector::class);
    $config->rule(UpdateUriImportsRector::class);

    $config->ruleWithConfiguration(RenameClassRector::class, [
        'Tempest\Database\Id' => 'Tempest\Database\PrimaryKey',
        'Tempest\CommandBus\AsyncCommand' => 'Tempest\CommandBus\Async',
        'Tempest\Validation\Rules\AlphaNumeric' => 'Tempest\Validation\Rules\IsAlphaNumeric',
        'Tempest\Validation\Rules\ArrayList' => 'Tempest\Validation\Rules\IsArrayList',
        'Tempest\Validation\Rules\BeforeDate' => 'Tempest\Validation\Rules\IsBeforeDate',
        'Tempest\Validation\Rules\Between' => 'Tempest\Validation\Rules\IsBetween',
        'Tempest\Validation\Rules\BetweenDates' => 'Tempest\Validation\Rules\IsBetweenDates',
        'Tempest\Validation\Rules\Count' => 'Tempest\Validation\Rules\HasCount',
        'Tempest\Validation\Rules\DateTimeFormat' => 'Tempest\Validation\Rules\HasDateTimeFormat',
        'Tempest\Validation\Rules\DivisibleBy' => 'Tempest\Validation\Rules\IsDivisibleBy',
        'Tempest\Validation\Rules\Email' => 'Tempest\Validation\Rules\IsEmail',
        'Tempest\Validation\Rules\EndsWith' => 'Tempest\Validation\Rules\EndsWith',
        'Tempest\Validation\Rules\Even' => 'Tempest\Validation\Rules\IsEvenNumber',
        'Tempest\Validation\Rules\HexColor' => 'Tempest\Validation\Rules\IsHexColor',
        'Tempest\Validation\Rules\IP' => 'Tempest\Validation\Rules\IsIP',
        'Tempest\Validation\Rules\IPv4' => 'Tempest\Validation\Rules\IsIPv4',
        'Tempest\Validation\Rules\IPv6' => 'Tempest\Validation\Rules\IsIPv6',
        'Tempest\Validation\Rules\In' => 'Tempest\Validation\Rules\IsIn',
        'Tempest\Validation\Rules\Json' => 'Tempest\Validation\Rules\IsJsonString',
        'Tempest\Validation\Rules\Length' => 'Tempest\Validation\Rules\HasLength',
        'Tempest\Validation\Rules\Lowercase' => 'Tempest\Validation\Rules\IsLowercase',
        'Tempest\Validation\Rules\MACAddress' => 'Tempest\Validation\Rules\IsMacAddress',
        'Tempest\Validation\Rules\MultipleOf' => 'Tempest\Validation\Rules\IsMultipleOf',
        'Tempest\Validation\Rules\NotEmpty' => 'Tempest\Validation\Rules\IsNotEmptyString',
        'Tempest\Validation\Rules\NotIn' => 'Tempest\Validation\Rules\IsNotIn',
        'Tempest\Validation\Rules\NotNull' => 'Tempest\Validation\Rules\IsNotNull',
        'Tempest\Validation\Rules\Numeric' => 'Tempest\Validation\Rules\IsNumeric',
        'Tempest\Validation\Rules\Odd' => 'Tempest\Validation\Rules\IsOddNumber',
        'Tempest\Validation\Rules\Password' => 'Tempest\Validation\Rules\IsPassword', // @mago-expect lint:no-literal-password
        'Tempest\Validation\Rules\PhoneNumber' => 'Tempest\Validation\Rules\IsPhoneNumber',
        'Tempest\Validation\Rules\RegEx' => 'Tempest\Validation\Rules\MatchesRegEx',
        'Tempest\Validation\Rules\Time' => 'Tempest\Validation\Rules\IsTime',
        'Tempest\Validation\Rules\Timestamp' => 'Tempest\Validation\Rules\IsUnixTimestamp',
        'Tempest\Validation\Rules\Timezone' => 'Tempest\Validation\Rules\IsTimezone',
        'Tempest\Validation\Rules\Ulid' => 'Tempest\Validation\Rules\IsUlid',
        'Tempest\Validation\Rules\Uppercase' => 'Tempest\Validation\Rules\IsUppercase',
        'Tempest\Validation\Rules\Url' => 'Tempest\Validation\Rules\IsUrl',
        'Tempest\Validation\Rules\Uuid' => 'Tempest\Validation\Rules\IsUuid',
    ]);

    $config->ruleWithConfiguration(RenamePropertyRector::class, [
        new RenameProperty(
            type: 'Tempest\Database\PrimaryKey',
            oldProperty: 'id',
            newProperty: 'value',
        ),
    ]);

    $config->rule(RemoveIdImportRector::class);
    $config->rule(RemoveDatabaseMigrationImportRector::class);
};
