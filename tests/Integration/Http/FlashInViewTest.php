<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class FlashInViewTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function flash_value_consumed_while_rendering_a_view_is_shown_once(): void
    {
        // A request flashes a value and redirects.
        $this->http
            ->post('/flash-view')
            ->assertRedirect('/flash-view');

        // The redirected request reads the flash value while rendering the view.
        $this->http
            ->get('/flash-view')
            ->assertOk()
            ->assertSee('flashed-message');

        // The following request no longer sees it: it was aged out at the start of
        // this request, even though it was only ever read while rendering the view.
        $this->http
            ->get('/flash-view')
            ->assertOk()
            ->assertNotSee('flashed-message');
    }
}
