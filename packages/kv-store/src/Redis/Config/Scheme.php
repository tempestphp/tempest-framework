<?php

namespace Tempest\KeyValue\Redis\Config;

/**
 * Represents the connection scheme used to connect to Redis (predis only).
 */
enum Scheme: string
{
    /**
     * Connect to Redis using TCP/IP.
     */
    case TCP = 'tcp';

    /**
     * Connect to Redis using TCP/IP with TLS.
     */
    case TLS = 'tls';

    /**
     * Connect to Redis using unix domain socket.
     */
    case UNIX = 'unix';
}
