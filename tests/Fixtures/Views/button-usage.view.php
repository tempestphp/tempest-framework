<?php

use Tests\Tempest\Fixtures\Controllers\DocsController;
use function Tempest\uri;

?>

<x-button uri="<?= uri(DocsController::class, category: 'framework', slug: '01-getting-started') ?>">Read the docs</x-button>