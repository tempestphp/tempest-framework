<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Masterminds\HTML5;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\Elements\GenericElement;
use Tempest\View\Elements\TextElement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ElementFactoryTest extends FrameworkIntegrationTestCase
{
    public function test_parental_relations(): void
    {
        $contents = <<<'HTML'
        <a>
            <b>
                <c>
                    Hello
                </c>
                <d />
                <e />
            </b>    
        </a>
        HTML;

        $html5 = new HTML5();
        $dom = $html5->loadHTML("<div id='tempest_render'>{$contents}</div>");

        $elementFactory = $this->container->get(ElementFactory::class);

        $a = $elementFactory->make($dom->getElementById('tempest_render')->firstElementChild);

        $this->assertInstanceOf(GenericElement::class, $a);
        $this->assertCount(1, $a->getChildren());
        $this->assertNull($a->getParent());

        $b = $a->getChildren()[0];
        $this->assertInstanceOf(GenericElement::class, $b);
        $this->assertCount(3, $b->getChildren());
        $this->assertSame($b->getParent(), $a);

        $c = $b->getChildren()[0];
        $this->assertInstanceOf(GenericElement::class, $c);
        $this->assertCount(1, $c->getChildren());
        $this->assertSame($c->getParent(), $b);

        $text = $c->getChildren()[0];
        $this->assertInstanceOf(TextElement::class, $text);
        $this->assertSame($text->getParent(), $c);

        $d = $b->getChildren()[1];
        $this->assertInstanceOf(GenericElement::class, $d);
        $this->assertCount(0, $d->getChildren());
        $this->assertSame($d->getParent(), $b);
        $this->assertSame($d->getPrevious(), $c);

        $e = $b->getChildren()[2];
        $this->assertInstanceOf(GenericElement::class, $e);
        $this->assertCount(0, $e->getChildren());
        $this->assertSame($e->getParent(), $b);
        $this->assertSame($e->getPrevious(), $d);
    }
}
