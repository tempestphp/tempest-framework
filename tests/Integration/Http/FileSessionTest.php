<?php

namespace Tests\Tempest\Integration\Http;

use Tempest\Http\Session\Session;
use Tempest\Testing\IntegrationTest;

final class FileSessionTest extends IntegrationTest
{
    /** @test */
    public function create_session() 
    {
        $session = $this->container->get(Session::class);

        $session->put('test', 'test');

        $session->destroy();
        dd($session);
    }
}