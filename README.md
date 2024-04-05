# tempest/console

```php
composer require tempest/console
```

Getting started:

```php
#!/usr/bin/env php
<?php

use Tempest\Console\ConsoleApplication;

require_once getcwd() . '/vendor/autoload.php';

ConsoleApplication::boot('My Console')->run();

exit;
```