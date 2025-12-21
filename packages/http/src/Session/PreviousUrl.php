<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Http\Method;
use Tempest\Http\Request;

/**
 * Tracks the previous URL visited by the user.
 */
final readonly class PreviousUrl
{
    private const string PREVIOUS_URL_SESSION_KEY = '#previous_url';
    private const string INTENDED_URL_SESSION_KEY = '#intended_url';

    public function __construct(
        private Session $session,
    ) {}

    /**
     * Stores the current request URL as the previous URL.
     */
    public function track(Request $request): void
    {
        if ($this->shouldNotTrack($request)) {
            return;
        }

        $this->session->set(self::PREVIOUS_URL_SESSION_KEY, $request->uri);
    }

    /**
     * Gets the previous URL, or a default fallback.
     */
    public function get(string $default = '/'): string
    {
        return $this->session->get(self::PREVIOUS_URL_SESSION_KEY, $default);
    }

    /**
     * Stores the URL where user was trying to go before being redirected. After authentication, the user should be redirect to that URL.
     */
    public function setIntended(string $url): void
    {
        $this->session->set(self::INTENDED_URL_SESSION_KEY, $url);
    }

    /**
     * Gets and consume the intended URL.
     */
    public function getIntended(string $default = '/'): string
    {
        return $this->session->consume(self::INTENDED_URL_SESSION_KEY, $default);
    }

    private function shouldNotTrack(Request $request): bool
    {
        if ($request->headers->get('x-requested-with') === 'XMLHttpRequest') {
            return true;
        }

        if ($request->method !== Method::GET) {
            return true;
        }

        if ($request->headers->get('purpose') === 'prefetch') {
            return true;
        }

        return false;
    }
}
