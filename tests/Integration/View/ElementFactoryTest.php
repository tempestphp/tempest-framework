<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Tempest\View\Element;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\Elements\GenericElement;
use Tempest\View\Elements\RootElement;
use Tempest\View\Elements\TextElement;
use Tempest\View\Elements\WhitespaceElement;
use Tempest\View\Parser\TempestViewParser;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\arr;

/**
 * @internal
 */
final class ElementFactoryTest extends FrameworkIntegrationTestCase
{
    public function test_parental_relations(): void
    {
        // See https://github.com/php/php-src/issues/16960
        $contents = <<<'HTML'
        <a>
            <b>
                <c>
                    Hello
                </c>
                <d></d>
                <e></e>
            </b>    
        </a>
        HTML;

        $ast = TempestViewParser::ast($contents);

        $elementFactory = $this->container->get(ElementFactory::class);

        $root = new RootElement();
        $elementFactory->make(iterator_to_array($ast)[0], $root);
        $a = $root->getChildren()[0];

        $this->assertInstanceOf(GenericElement::class, $a);
        $this->assertCount(1, $this->withoutWhitespace($a->getChildren()));
        $this->assertInstanceOf(RootElement::class, $a->getParent());

        $b = $this->withoutWhitespace($a->getChildren())[0];
        $this->assertInstanceOf(GenericElement::class, $b);
        $this->assertCount(3, $this->withoutWhitespace($b->getChildren()));
        $this->assertSame($b->getParent(), $a);

        $c = $this->withoutWhitespace($b->getChildren())[0];
        $this->assertInstanceOf(GenericElement::class, $c);
        $this->assertCount(1, $this->withoutWhitespace($c->getChildren()));
        $this->assertSame($c->getParent(), $b);

        $text = $this->withoutWhitespace($c->getChildren())[0];
        $this->assertInstanceOf(TextElement::class, $text);
        $this->assertSame($text->getParent(), $c);

        $d = $this->withoutWhitespace($b->getChildren())[1];
        $this->assertInstanceOf(GenericElement::class, $d);
        $this->assertCount(0, $this->withoutWhitespace($d->getChildren()));
        $this->assertSame($d->getParent(), $b);
        $this->assertSame($d->getPrevious(), $c);

        $e = $this->withoutWhitespace($b->getChildren())[2];
        $this->assertInstanceOf(GenericElement::class, $e);
        $this->assertCount(0, $this->withoutWhitespace($e->getChildren()));
        $this->assertSame($e->getParent(), $b);
        $this->assertSame($e->getPrevious(), $d);
    }

    private function withoutWhitespace(array $elements): array
    {
        return arr($elements)
            ->filter(fn (Element $element) => ! $element instanceof WhitespaceElement)
            ->values()
            ->toArray();
    }
}
