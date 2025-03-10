<?php

namespace Tempest\Debug;

use Dom\Comment;
use Dom\Document;
use Dom\DocumentType;
use Dom\Element as DomElement;
use Dom\Node;
use Dom\NodeList;
use Dom\Text;

final readonly class DOMDebug
{
    public static function dump(Document|NodeList $dom): void
    {
        if ($dom instanceof Document) {
            $dom = $dom->childNodes;
        }

        $content = [];

        foreach ($dom as $node) {
            $content[] = self::dumpNode($node);
        }

        lw(implode(PHP_EOL, $content));
    }

    private static function dumpNode(Node $node, int $depth = 0): string
    {
        $content = str_repeat(' ', $depth * 4);

        $content .= match (true) {
            $node instanceof DocumentType => 'doctype',
            $node instanceof Text => trim($node->textContent) !== '' ? 'text' : '',
            $node instanceof Comment => '// comment',
            $node instanceof DomElement => "<{$node->tagName}>",
            default => 'unknown',
        };

        foreach ($node->childNodes as $child) {
            $content .= PHP_EOL . self::dumpNode($child, $depth + 1);
        }

        return $content;
    }
}
