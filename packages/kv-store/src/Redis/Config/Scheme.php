<?php

namespace Tempest\KeyValue\Redis\Config;

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
