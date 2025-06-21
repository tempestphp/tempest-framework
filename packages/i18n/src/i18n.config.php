<?php

use Tempest\Internationalization\InternationalizationConfig;
use Tempest\Support\Language\Locale;

return new InternationalizationConfig(
    currentLocale: Locale::default(),
    fallbackLocale: Locale::default(),
);
