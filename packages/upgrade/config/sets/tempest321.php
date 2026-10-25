<?php

use Rector\Config\RectorConfig;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Renaming\Rector\Name\RenameClassRector;

return static function (RectorConfig $config): void {
    SimpleParameterProvider::setParameter(Option::AUTO_IMPORT_NAMES, value: true);
    SimpleParameterProvider::setParameter(Option::IMPORT_SHORT_CLASSES, value: true);

    $config->ruleWithConfiguration(RenameClassRector::class, [
        'Tempest\Idempotency\Attributes\Idempotent' => 'Tempest\Idempotency\Idempotent',
        'Tempest\Idempotency\Attributes\IdempotentRoute' => 'Tempest\Idempotency\IdempotentRoute',
        'Tempest\Idempotency\Config\IdempotencyConfig' => 'Tempest\Idempotency\IdempotencyConfig',
        'Tempest\Idempotency\Exceptions\IdempotencyException' => 'Tempest\Idempotency\IdempotencyException',
        'Tempest\Idempotency\Exceptions\IdempotencyKeyWasAlreadyUsed' => 'Tempest\Idempotency\IdempotencyKeyWasAlreadyUsed',
        'Tempest\Idempotency\Exceptions\IdempotencyMethodWasNotSupported' => 'Tempest\Idempotency\IdempotencyMethodWasNotSupported',
        'Tempest\Idempotency\Exceptions\IdempotencyPlatformWasNotSupported' => 'Tempest\Idempotency\IdempotencyPlatformWasNotSupported',
        'Tempest\Idempotency\Fingerprint\CommandFingerprintGenerator' => 'Tempest\Idempotency\CommandFingerprintGenerator',
        'Tempest\Idempotency\Fingerprint\HttpFingerprintGenerator' => 'Tempest\Idempotency\HttpFingerprintGenerator',
        'Tempest\Idempotency\Store\CacheIdempotencyStore' => 'Tempest\Idempotency\CacheIdempotencyStore',
        'Tempest\Idempotency\Store\IdempotencyRecord' => 'Tempest\Idempotency\IdempotencyRecord',
        'Tempest\Idempotency\Store\IdempotencyState' => 'Tempest\Idempotency\IdempotencyState',
        'Tempest\Idempotency\Store\IdempotencyStore' => 'Tempest\Idempotency\IdempotencyStore',
        'Tempest\Idempotency\Store\StoredResponse' => 'Tempest\Idempotency\StoredResponse',
    ]);
};
