<?php

use Tempest\DateTime\Duration;
use Tempest\Icon\IconConfig;

return new IconConfig(
    iconifyApiUrl: 'https://api.iconify.design',
    retryAfter: Duration::hours(12),
    expiresAfter: null,
);
