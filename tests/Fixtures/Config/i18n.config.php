<?php

use Tempest\Intl\IntlConfig;
use Tempest\Intl\Locale;

return new IntlConfig(
    currentLocale: Locale::ENGLISH_UNITED_STATES,
    fallbackLocale: Locale::ENGLISH_UNITED_STATES,
);
