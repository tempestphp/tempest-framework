<?php

use Tests\Tempest\Fixtures\Views\DocsView;

/** @var DocsView $this */
?>

<x-base :title="$this->currentChapter->title">
    <div :if="$this->nextChapter()">
        next: <?= $this->nextChapter()->title ?>
    </div>
</x-base>
