<?php

declare(strict_types=1);

namespace Tempest\Support\Ip;

/**
 * Ranges that are not routed on the public internet, and may only be reached from within the network the application runs on.
 *
 * @var string[]
 */
const PRIVATE_RANGES = [
    '127.0.0.0/8', // Loopback (RFC 1122)
    '10.0.0.0/8', // Private-use (RFC 1918)
    '172.16.0.0/12', // Private-use (RFC 1918)
    '192.168.0.0/16', // Private-use (RFC 1918)
    '169.254.0.0/16', // Link local (RFC 3927)
    '0.0.0.0/8', // This network (RFC 1122)
    '240.0.0.0/4', // Reserved (RFC 1112)
    '::1/128', // Loopback (RFC 4291)
    'fc00::/7', // Unique local (RFC 4193)
    'fe80::/10', // Link local (RFC 4291)
    '::/128', // Unspecified (RFC 4291)
];
