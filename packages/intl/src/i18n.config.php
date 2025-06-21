<?php

use Tempest\Intl\InternationalizationConfig;
use Tempest\Intl\Locale;

return new InternationalizationConfig(
    currentLocale: Locale::default(),
    fallbackLocale: Locale::default(),
);
