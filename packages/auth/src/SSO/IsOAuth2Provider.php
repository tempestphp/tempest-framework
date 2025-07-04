<?php

declare(strict_types=1);

namespace Tempest\Auth\SSO;
use Tempest\HttpClient\HttpClient;

trait IsOAuth2Provider
{
    private(set) string $scopeSeparator = ' ';
//
//    abstract protected string $userDataUrl {
//        get;
//    }

    public function __construct(
        private readonly HttpClient $httpClient,
    ) {}

//    public function getUserData(array $headers = []): OAuth2UserData
//    {
//        $this->httpClient->get(
//            uri: $this->userDataUrl,
//            headers: $headers
//        );
//    }
}
