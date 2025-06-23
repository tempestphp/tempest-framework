<?php

declare(strict_types=1);

namespace Tempest\HttpClient\Testing;

use Psr\Http\Message\ResponseInterface;

final class ResponseBag
{
    /** @var array<array-key,ResponseInterface> */
    private array $responses = [];

    private int $nextResponse = 0;

    private bool $randomize = false;

    /**
     * @param array<array-key,ResponseInterface> $sequence
     */
    public function __construct(array $sequence = [])
    {
        foreach ($sequence as $response) {
            $this->addResponse($response);
        }
    }

    public function addResponse(ResponseInterface $response): self
    {
        $this->responses[] = $response;

        return $this;
    }

    public function randomize(bool $randomize = true): self
    {
        $this->randomize = $randomize;

        return $this;
    }

    public function getNextResponse(): ResponseInterface
    {
        if ($this->randomize) {
            return $this->responses[array_rand($this->responses)];
        }

        $response = $this->responses[$this->nextResponse];

        if ($this->nextResponse === (count($this->responses) - 1)) {
            $this->nextResponse = 0;
        } else {
            $this->nextResponse++;
        }

        return $response;
    }
}
