<?php

declare(strict_types=1);

namespace Tempest\Mcp\Testing;

use PHPUnit\Framework\Assert;

use function Tempest\Support\Json\encode;

final readonly class McpTestResponse
{
    public function __construct(
        public array $response,
    ) {}

    public function result(): ?array
    {
        return $this->response['result'] ?? null;
    }

    public function error(): ?array
    {
        return $this->response['error'] ?? null;
    }

    public function assertOk(): self
    {
        Assert::assertArrayHasKey('result', $this->response, sprintf(
            'Expected a successful result, but got the error: %s',
            $this->response['error']['message'] ?? 'unknown',
        ));

        Assert::assertNotTrue(
            $this->result()['isError'] ?? false,
            sprintf('Expected a successful result, but the call errored: %s', encode($this->result())),
        );

        return $this;
    }

    public function assertError(?string $message = null): self
    {
        $isProtocolError = isset($this->response['error']);
        $isToolError = ($this->result()['isError'] ?? false) === true;

        Assert::assertTrue($isProtocolError || $isToolError, 'Expected an error, but the call succeeded.');

        if ($message !== null) {
            $haystack = $isProtocolError
                ? $this->response['error']['message'] ?? ''
                : implode("\n", $this->texts());

            Assert::assertStringContainsString($message, $haystack);
        }

        return $this;
    }

    public function assertText(string $text): self
    {
        Assert::assertContains($text, $this->texts());

        return $this;
    }

    public function assertTextContains(string $needle): self
    {
        Assert::assertStringContainsString($needle, implode("\n", $this->texts()));

        return $this;
    }

    public function assertStructured(array $expected): self
    {
        Assert::assertSame($expected, $this->result()['structuredContent'] ?? null);

        return $this;
    }

    public function assertSee(string $needle): self
    {
        Assert::assertStringContainsString($needle, encode($this->response));

        return $this;
    }

    public function assertToolListed(string $name): self
    {
        $tools = array_column($this->result()['tools'] ?? [], 'name');

        Assert::assertContains($name, $tools);

        return $this;
    }

    /**
     * Collects all text values from tool content, prompt messages and resource contents.
     *
     * @return string[]
     */
    private function texts(): array
    {
        $result = $this->result() ?? [];

        $texts = array_column($result['content'] ?? [], 'text');

        foreach ($result['messages'] ?? [] as $message) {
            if (! isset($message['content']['text'])) {
                continue;
            }

            $texts[] = $message['content']['text'];
        }

        $texts = [...$texts, ...array_column($result['contents'] ?? [], 'text')];

        return array_filter($texts, is_string(...));
    }
}
