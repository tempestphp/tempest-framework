<?php

declare(strict_types=1);

namespace Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tempest\Cryptography\Encryption\Encrypter;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieConfig;
use Tempest\Http\Request;
use Tempest\Http\Responses\Ok;
use Tempest\Reflection\MethodReflector;
use Tempest\Router\Get;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class CookieHandlingTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function encrypted_cookies_are_kept_when_default(): void
    {
        try {
            $encrypter = $this->container->get(Encrypter::class);
            $_COOKIE['Cookie_name'] = $encrypter->encrypt('myCookieValue')->serialize();

            $responseHelper = $this->http
                ->registerRoute($this->returnCookieValueController())
                ->get('/get_cookie_value')
                ->assertOk()
                ->assertSee('myCookieValue');

            foreach ($responseHelper->headers as $header) {
                if ($header->name !== 'set-cookie') {
                    continue;
                }

                foreach ($header->values as $value) {
                    $this->assertNotEquals(
                        $value,
                        'Cookie_name=; Expires=Wed, 31-Dec-1969 23:59:59 GMT; Max-Age=0; Path=/; Secure; SameSite=Lax',
                    );
                }
            }
        } finally {
            unset($_COOKIE['Cookie_name']);
        }
    }

    #[Test]
    public function unencrypted_cookies_are_discarded_when_default(): void
    {
        try {
            $_COOKIE['Cookie_name'] = 'myCookieValue';

            $this->http
                ->registerRoute($this->returnCookieValueController())
                ->get('/get_cookie_value')
                ->assertOk()
                ->assertHeaderMatches('set-cookie', 'Cookie_name=; Expires=Wed, 31-Dec-1969 23:59:59 GMT; Max-Age=0; Path=/; Secure; SameSite=Lax')
                ->assertNotSee('myCookieValue');
        } finally {
            unset($_COOKIE['Cookie_name']);
        }
    }

    #[Test]
    public function unencrypted_cookies_are_kept_when_discard_false(): void
    {
        $this->container->config(new CookieConfig(discardUnencryptedCookies: false));

        try {
            $_COOKIE['Cookie_name'] = 'myCookieValue';

            $responseHelper = $this->http
                ->registerRoute($this->returnCookieValueController())
                ->get('/get_cookie_value')
                ->assertOk()
                ->assertNotSee('myCookieValue'); // cookies are not discarded but not whitelisted so not available

            foreach ($responseHelper->headers as $header) {
                if ($header->name !== 'set-cookie') {
                    continue;
                }

                foreach ($header->values as $value) {
                    $this->assertNotEquals(
                        $value,
                        'Cookie_name=; Expires=Wed, 31-Dec-1969 23:59:59 GMT; Max-Age=0; Path=/; Secure; SameSite=Lax',
                    );
                }
            }
        } finally {
            unset($_COOKIE['Cookie_name']);
        }
    }

    #[Test]
    public function unencrypted_cookies_are_discarded_when_discard_true(): void
    {
        $this->container->config(new CookieConfig(discardUnencryptedCookies: true));

        try {
            $_COOKIE['Cookie_name'] = 'myCookieValue';

            $this->http
                ->registerRoute($this->returnCookieValueController())
                ->get('/get_cookie_value')
                ->assertOk()
                ->assertHeaderMatches('set-cookie', 'Cookie_name=; Expires=Wed, 31-Dec-1969 23:59:59 GMT; Max-Age=0; Path=/; Secure; SameSite=Lax')
                ->assertNotSee('myCookieValue');
        } finally {
            unset($_COOKIE['Cookie_name']);
        }
    }

    #[Test]
    public function whitelisted_plaintext_cookies_are_kept(): void
    {
        $this->container->config(new CookieConfig(
            discardUnencryptedCookies: true,
            plaintextCookies: ['Cookie_name'],
        ));

        try {
            $_COOKIE['Cookie_name'] = 'myCookieValue';

            $responseHelper = $this->http
                ->registerRoute($this->returnCookieValueController())
                ->get('/get_cookie_value')
                ->assertOk()
                ->assertSee('myCookieValue');

            foreach ($responseHelper->headers as $header) {
                if ($header->name !== 'set-cookie') {
                    continue;
                }

                foreach ($header->values as $value) {
                    $this->assertNotEquals(
                        $value,
                        'Cookie_name=; Expires=Wed, 31-Dec-1969 23:59:59 GMT; Max-Age=0; Path=/; Secure; SameSite=Lax',
                    );
                }
            }
        } finally {
            unset($_COOKIE['Cookie_name']);
        }
    }

    #[Test]
    public function whitelisted_plaintext_cookies_are_send_in_plain(): void
    {
        $this->container->config(new CookieConfig(
            plaintextCookies: ['Cookie_name'],
        ));

        $controller = new class {
            #[Get('/test_whitelisted_unencrypted_cookies_are_send_in_plain')]
            public function __invoke(): Ok
            {
                return new Ok()->addCookie(
                    new Cookie(
                        key: 'Cookie_name',
                        value: 'value',
                    ),
                );
            }
        };

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('__invoke');

        $this->http
            ->registerRoute(new MethodReflector($method))
            ->get('/test_whitelisted_unencrypted_cookies_are_send_in_plain')
            ->assertOk()
            ->assertHeaderMatches('set-cookie', 'Cookie_name=value; Path=/; Secure; SameSite=Lax');
    }

    private function returnCookieValueController(): MethodReflector
    {
        $controller = new class() {
            #[Get('/get_cookie_value')]
            public function __invoke(Request $request): Ok
            {
                return new Ok(
                    $request->getCookie('Cookie_name')->value ?? '',
                );
            }
        };

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('__invoke');

        return new MethodReflector($method);
    }
}
