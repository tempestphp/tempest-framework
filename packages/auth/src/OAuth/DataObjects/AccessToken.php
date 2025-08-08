<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\DataObjects;

use Tempest\Mapper\MapFrom;

use function Tempest\map;

final readonly class AccessToken
{
    public function __construct(
        #[MapFrom('access_token')]
        public string $accessToken,

        #[MapFrom('token_type')]
        public string $tokenType,

        public string $scope,
    ) {}

    public static function from(array $data): self
    {
        return map($data)->to(self::class);
    }
}
