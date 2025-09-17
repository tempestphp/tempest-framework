<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\DataObjects;

use League\OAuth2\Client\Token\AccessToken as LeagueAccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
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

        #[MapFrom('expires_in')]
        public ?int $expiresIn = null,

        #[MapFrom('refresh_token')]
        public ?string $refreshToken = null,

        #[MapFrom('additional_informations')]
        public ?array $additionalInformations = null,
    ) {}

    public static function from(array $data): self
    {
        return map($data)->to(self::class);
    }

    public static function fromLeagueAccessToken(AccessTokenInterface $accessToken): self
    {
        return new self(
            accessToken: $accessToken->getToken(),
            tokenType: $accessToken->getValues()['token_type'] ?? 'Bearer',
            scope: $accessToken->getValues()['scope'] ?? '',
            expiresIn: $accessToken->getExpires()
                ? ($accessToken->getExpires() - time())
                : null,
            refreshToken: $accessToken->getRefreshToken(),
            additionalInformations: $accessToken->getValues() ?: null,
        );
    }

    public function toLeagueAccessToken(): LeagueAccessToken
    {
        return new LeagueAccessToken([
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_in' => $this->expiresIn,
            ...$this->additionalInformations,
        ]);
    }
}
