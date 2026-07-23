<?php

declare(strict_types=1);

namespace Tempest\Mcp\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Mcp\UriTemplate;

/**
 * @internal
 */
final class UriTemplateTest extends TestCase
{
    #[Test]
    public function extracts_variable_names(): void
    {
        $template = new UriTemplate('demo://users/{id}/posts/{postId}');

        $this->assertTrue($template->isTemplated());
        $this->assertSame(['id', 'postId'], $template->variableNames);
    }

    #[Test]
    public function plain_uris_are_not_templated(): void
    {
        $template = new UriTemplate('demo://config');

        $this->assertFalse($template->isTemplated());
        $this->assertSame([], $template->variableNames);
    }

    #[Test]
    public function matches_uris_against_the_template(): void
    {
        $template = new UriTemplate('demo://users/{id}/posts/{postId}');

        $this->assertSame(['id' => '42', 'postId' => '7'], $template->match('demo://users/42/posts/7'));
        $this->assertNull($template->match('demo://users/42'));
        $this->assertNull($template->match('demo://users/42/posts/7/comments'));
        $this->assertNull($template->match('other://users/42/posts/7'));
    }

    #[Test]
    public function variables_do_not_match_across_segments(): void
    {
        $template = new UriTemplate('demo://users/{id}');

        $this->assertNull($template->match('demo://users/42/posts'));
        $this->assertSame(['id' => 'jon-doe'], $template->match('demo://users/jon-doe'));
    }

    #[Test]
    public function percent_encoded_variables_are_decoded(): void
    {
        $template = new UriTemplate('demo://users/{id}');

        $this->assertSame(['id' => 'jon doe'], $template->match('demo://users/jon%20doe'));
        $this->assertSame(['id' => 'a/b'], $template->match('demo://users/a%2Fb'));
    }

    #[Test]
    public function special_characters_in_literals_are_escaped(): void
    {
        $template = new UriTemplate('demo://search.json?q={query}');

        $this->assertSame(['query' => 'tempest'], $template->match('demo://search.json?q=tempest'));
        $this->assertNull($template->match('demo://searchXjson?q=tempest'));
    }

    #[Test]
    public function converts_to_string(): void
    {
        $this->assertSame('demo://users/{id}', (string) new UriTemplate('demo://users/{id}'));
    }
}
