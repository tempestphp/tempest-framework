<?php

namespace Tempest\View\Tests;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\View\Parser\Token;
use Tempest\View\Parser\TokenType;

final class TokenTest extends TestCase
{
    #[TestWith(['div', '<div/>', TokenType::SELF_CLOSING_TAG])]
    #[TestWith(['div', '<div />', TokenType::SELF_CLOSING_TAG])]
    #[TestWith(['div', '<div', TokenType::OPEN_TAG_START])]
    #[TestWith(['div', '<div>', TokenType::OPEN_TAG_START])]
    #[TestWith(['div', '<div foo="bar">', TokenType::OPEN_TAG_START])]
    #[TestWith(['div', '</div>', TokenType::CLOSING_TAG])]
    #[TestWith([null, '<?=', TokenType::PHP])]
    public function test_tag(?string $expectedTag, string $html, TokenType $type): void
    {
        $this->assertSame($expectedTag, new Token($html, $type)->tag);
    }
}