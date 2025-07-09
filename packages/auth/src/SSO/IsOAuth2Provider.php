<?php

declare(strict_types=1);

namespace Tempest\Auth\SSO;
use Tempest\Container\Inject;
use Tempest\HttpClient\HttpClient;

trait IsOAuth2Provider
{
    #[Inject]
    private readonly HttpClient $httpClient;

    private(set) string $scopeSeparator = ' ';

    //
//    abstract protected string $userDataUrl {
//        get;
//    }


//    public function getUserData(array $headers = []): OAuth2UserData
//    {
//        $this->httpClient->get(
//            uri: $this->userDataUrl,
//            headers: $headers
//        );
//    }
}
