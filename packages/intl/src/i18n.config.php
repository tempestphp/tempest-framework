<?php

use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;

return new IntlConfig(
    currentLocale: Locale::default(),
    fallbackLocale: Locale::default(),
);
