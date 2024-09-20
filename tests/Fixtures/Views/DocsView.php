<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Views;

use Tempest\View\IsView;
use Tempest\View\View;

final class DocsView implements View
{
    use IsView;

    public function __construct(
        public Chapter $currentChapter,
    ) {
        $this->path = __DIR__ . '/docs.view.php';
    }

    public function nextChapter(): Chapter
    {
        return new Chapter('Next Title');
    }
}
