<?php

declare(strict_types=1);

namespace Tempest\Support\Uri;

/**
 * Updates the given URI to include the provided query parameters. Previous parameters are removed.
 */
function set_query(string $uri, mixed ...$query): string
{
    return Uri::from($uri)->withQuery(...$query)->toString();
}

/**
 * Adds query parameters to the existing ones in the URI.
 */
function merge_query(string $uri, mixed ...$query): string
{
    return Uri::from($uri)->addQuery(...$query)->toString();
}

/**
 * Removes specific query parameters from the URI.
 */
function without_query(string $uri, mixed ...$query): string
{
    if (count($query) === 0) {
        return Uri::from($uri)->removeQuery()->toString();
    }

    return Uri::from($uri)->withoutQuery(...$query)->toString();
}

/**
 * Extracts the query parameters from the given URI as an associative array.
 */
function get_query(string $uri): array
{
    return Uri::from($uri)->query;
}

/**
 * Updates the given URI to include the provided fragment.
 */
function set_fragment(string $uri, string $fragment): string
{
    return Uri::from($uri)->withFragment($fragment)->toString();
}

/**
 * Extracts the fragment from the given URI.
 */
function get_fragment(string $uri): ?string
{
    return Uri::from($uri)->fragment;
}

/**
 * Updates the given URI to use the provided host.
 */
function set_host(string $uri, string $host): string
{
    return Uri::from($uri)->withHost($host)->toString();
}

/**
 * Extracts the host from the given URI.
 */
function get_host(string $uri): ?string
{
    return Uri::from($uri)->host;
}

/**
 * Updates the given URI to use the provided scheme.
 */
function set_scheme(string $uri, string $scheme): string
{
    return Uri::from($uri)->withScheme($scheme)->toString();
}

/**
 * Extracts the scheme from the given URI.
 */
function get_scheme(string $uri): ?string
{
    return Uri::from($uri)->scheme;
}

/**
 * Updates the given URI to use the provided port.
 */
function set_port(string $uri, int $port): string
{
    return Uri::from($uri)->withPort($port)->toString();
}

/**
 * Extracts the port from the given URI.
 */
function get_port(string $uri): ?int
{
    return Uri::from($uri)->port;
}

/**
 * Updates the given URI to use the provided user.
 */
function set_user(string $uri, string $user): string
{
    return Uri::from($uri)->withUser($user)->toString();
}

/**
 * Extracts the user from the given URI.
 */
function get_user(string $uri): ?string
{
    return Uri::from($uri)->user;
}

/**
 * Updates the given URI to use the provided password.
 */
function set_password(string $uri, string $password): string
{
    return Uri::from($uri)->withPassword($password)->toString();
}

/**
 * Extracts the password from the given URI.
 */
function get_password(string $uri): ?string
{
    return Uri::from($uri)->password;
}

/**
 * Updates the given URI to use the provided path.
 */
function set_path(string $uri, string $path): string
{
    return Uri::from($uri)->withPath($path)->toString();
}

/**
 * Extracts the path from the given URI.
 */
function get_path(string $uri): ?string
{
    return Uri::from($uri)->path;
}

/**
 * Extracts the path segments from the URI as an array.
 */
function get_segments(string $uri): array
{
    return Uri::from($uri)->segments;
}
