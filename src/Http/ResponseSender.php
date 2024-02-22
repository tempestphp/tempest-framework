<?php

namespace Tempest\Http;

interface ResponseSender
{
    public function send(Response $response): Response;
}