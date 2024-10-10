<?php

use function Tempest\uri;
use Tests\Tempest\Fixtures\Controllers\DocsController;

?>

<x-button uri="<?= uri(DocsController::class, category: 'framework', slug: '01-getting-started') ?>">Read the docs</x-button>