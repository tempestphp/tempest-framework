<?php

namespace Tempest\Intl\Catalog;

use DOMDocument;
use DOMDocumentType;
use DOMElement;
use DOMNodeList;
use DOMText;
use DOMXPath;
use IntlChar;
use RuntimeException;

final readonly class XliffParser
{
    private const string VERSION_1_NAMESPACE = 'urn:oasis:names:tc:xliff:document:1.2';

    private const string PGS_NAMESPACE = 'urn:oasis:names:tc:xliff:pgs:1.0';

    private const string VERSION_2_NAMESPACE = 'urn:oasis:names:tc:xliff:document:2.0';

    private function __construct() {}

    /**
     * @return array<array-key, string>
     */
    public static function parse(string $contents): array
    {
        $document = self::loadDocument($contents);
        $root = $document->documentElement;

        if (! $root instanceof DOMElement || $root->localName !== 'xliff') {
            throw new RuntimeException('Invalid XLIFF document: expected an xliff root element.');
        }

        return match ($root->getAttribute('version')) {
            '1.2' => self::parseVersion1($document, $root),
            '2.0', '2.1', '2.2' => self::parseVersion2($document, $root),
            default => throw new RuntimeException('Unsupported XLIFF version: ' . $root->getAttribute('version')),
        };
    }

    private static function loadDocument(string $contents): DOMDocument
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);

        try {
            if (! $document->loadXML($contents, LIBXML_NONET | LIBXML_COMPACT)) {
                throw new RuntimeException('Invalid XLIFF document: malformed XML.');
            }

            if ($document->doctype instanceof DOMDocumentType) {
                throw new RuntimeException('Invalid XLIFF document: document types are not allowed.');
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return $document;
    }

    /**
     * @return array<string, string>
     */
    private static function parseVersion1(DOMDocument $document, DOMElement $root): array
    {
        if ($root->namespaceURI !== self::VERSION_1_NAMESPACE) {
            throw new RuntimeException('Invalid XLIFF 1.2 namespace.');
        }

        $xpath = self::createXPath($document, self::VERSION_1_NAMESPACE);
        /** @var DOMNodeList|false $units */
        $units = $xpath->query('//xliff:trans-unit');

        if (! $units instanceof DOMNodeList) {
            throw new RuntimeException('Could not read XLIFF 1.2 translation units.');
        }

        $identifiedMessages = [];
        $sourceAliases = [];

        foreach ($units as $unit) {
            if (! $unit instanceof DOMElement) {
                continue;
            }

            $source = self::findChild($xpath, $unit, 'source');
            $target = self::findChild($xpath, $unit, 'target');
            $sourceText = $source instanceof DOMElement ? self::extractText($source, $unit) : null;
            $targetText = $target instanceof DOMElement ? self::extractText($target, $unit) : null;
            $message = $targetText ?? $sourceText ?? '';
            $id = $unit->getAttribute('id');
            $resourceName = $unit->getAttribute('resname');

            if ($target instanceof DOMElement && $target->getAttribute('state') === 'needs-translation' && in_array($targetText, [$id, $resourceName, $sourceText], true)) {
                continue;
            }

            if ($sourceText !== null && $sourceText !== '') {
                $sourceAliases[$sourceText] = $message;
            }

            if ($id !== '') {
                $identifiedMessages[$id] = $message;
            }

            if ($resourceName !== '') {
                $identifiedMessages[$resourceName] = $message;
            }
        }

        return array_replace($sourceAliases, $identifiedMessages);
    }

    /**
     * @return array<array-key, string>
     */
    private static function parseVersion2(DOMDocument $document, DOMElement $root): array
    {
        if ($root->namespaceURI !== self::VERSION_2_NAMESPACE) {
            throw new RuntimeException('Invalid XLIFF 2.x namespace.');
        }

        $xpath = self::createXPath($document, self::VERSION_2_NAMESPACE);
        /** @var DOMNodeList|false $units */
        $units = $xpath->query('//xliff:unit');

        if (! $units instanceof DOMNodeList) {
            throw new RuntimeException('Could not read XLIFF 2.x translation units.');
        }

        $identifiedMessages = [];
        $sourceAliases = [];

        foreach ($units as $unit) {
            if (! $unit instanceof DOMElement) {
                continue;
            }

            if ($unit->hasAttributeNS(self::PGS_NAMESPACE, 'switch')) {
                $message = self::parsePgsUnit($xpath, $unit, $unit->getAttributeNS(self::PGS_NAMESPACE, 'switch'));
                $id = $unit->getAttribute('id');
                $name = $unit->getAttribute('name');

                if ($id !== '') {
                    $identifiedMessages[$id] = $message;
                }

                if ($name !== '') {
                    $identifiedMessages[$name] = $message;
                }

                continue;
            }

            /** @var list<array{index: int, order: int, message: string}> $parts */
            $parts = [];
            $position = 0;

            for ($index = 0; $index < $unit->childNodes->length; $index++) {
                $part = $unit->childNodes->item($index);
                if (! $part instanceof DOMElement) {
                    continue;
                }

                if (! in_array($part->localName, ['segment', 'ignorable'], true)) {
                    continue;
                }

                $position++;
                $source = self::findChild($xpath, $part, 'source');
                $target = self::findChild($xpath, $part, 'target');
                $sourceText = $source instanceof DOMElement ? self::extractText($source, $unit) : null;
                $targetText = $target instanceof DOMElement ? self::extractText($target, $unit) : null;
                $message = $targetText ?? $sourceText ?? '';
                $order = $target?->getAttribute('order');

                $parts[] = [
                    'index' => $position,
                    'order' => is_string($order) && ctype_digit($order) && (int) $order > 0 ? (int) $order : $position,
                    'message' => $message,
                ];

                if ($part->localName === 'segment' && $sourceText !== null && $sourceText !== '') {
                    $sourceAliases[$sourceText] = $message;
                }
            }

            usort(
                $parts,
                static fn (array $left, array $right): int => $left['order'] <=> $right['order'] ?: $left['index'] <=> $right['index'],
            );

            $message = '';

            foreach ($parts as $part) {
                $message .= $part['message'];
            }

            $id = $unit->getAttribute('id');
            $name = $unit->getAttribute('name');

            if ($id !== '') {
                $identifiedMessages[$id] = $message;
            }

            if ($name !== '') {
                $identifiedMessages[$name] = $message;
            }
        }

        return array_replace($sourceAliases, $identifiedMessages);
    }

    private static function parsePgsUnit(DOMXPath $xpath, DOMElement $unit, string $switch): string
    {
        $switches = self::parsePgsSwitch($switch);
        /** @var DOMNodeList|false $segments */
        $segments = $xpath->query('./xliff:segment', $unit);

        if (! $segments instanceof DOMNodeList) {
            throw new RuntimeException('Could not read XLIFF 2.2 PGS translation segments.');
        }

        $lines = [];

        foreach ($switches as $item) {
            $function = match ($item['type']) {
                'plural' => 'number',
                'ordinal' => 'number select=ordinal',
                default => 'string',
            };
            $lines[] = sprintf('.input {$%s :%s}', $item['variable'], $function);
        }

        $lines[] =
            '.match '
            . implode(' ', array_map(
                static fn (array $item): string => '$' . $item['variable'],
                $switches,
            ));

        foreach ($segments as $segment) {
            if (! $segment instanceof DOMElement) {
                continue;
            }

            if (! $segment->hasAttributeNS(self::PGS_NAMESPACE, 'case')) {
                continue;
            }

            $caseValues = self::splitWhitespace($segment->getAttributeNS(self::PGS_NAMESPACE, 'case'));

            if (count($caseValues) !== count($switches)) {
                throw new RuntimeException('Invalid XLIFF 2.2 PGS case: selector count does not match the switch.');
            }

            $messageElement = self::findChild($xpath, $segment, 'target') ?? self::findChild($xpath, $segment, 'source');

            if (! $messageElement instanceof DOMElement) {
                continue;
            }

            $keys = array_map(
                static fn (string $value): string => $value === 'other' ? '*' : ltrim($value, '='),
                $caseValues,
            );

            $lines[] = '    ' . implode(' ', $keys) . ' {{' . self::extractPgsText($messageElement, $unit) . '}}';
        }

        return implode("\n", $lines);
    }

    /**
     * @return list<array{type: string, variable: string}>
     */
    private static function parsePgsSwitch(string $switch): array
    {
        $tokens = self::splitWhitespace($switch);

        if ($tokens === []) {
            throw new RuntimeException('Invalid XLIFF 2.2 PGS switch: the switch must not be empty.');
        }

        $switches = [];

        foreach ($tokens as $token) {
            [$type, $variable] = array_pad(explode(':', $token, 2), 2, '');

            if (! in_array($type, ['gender', 'ordinal', 'plural', 'select'], true) || preg_match('/\A[a-z_][a-z0-9_-]*\z/i', $variable) !== 1) {
                throw new RuntimeException("Invalid XLIFF 2.2 PGS switch token: {$token}");
            }

            $switches[] = [
                'type' => $type,
                'variable' => $variable,
            ];
        }

        return $switches;
    }

    private static function extractPgsText(DOMElement $element, DOMElement $unit): string
    {
        $text = '';

        for ($index = 0; $index < $element->childNodes->length; $index++) {
            $child = $element->childNodes->item($index);

            if ($child instanceof DOMText) {
                $text .= $child->nodeValue ?? '';

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            if ($child->localName === 'ph' && $child->getAttribute('disp') !== '') {
                $text .= '{$' . $child->getAttribute('disp') . '}';

                continue;
            }

            $text .= match ($child->localName) {
                'cp' => self::extractCodePoint($child),
                'pc' => self::extractPairedCode($child, $unit),
                'bpt', 'bx', 'ec', 'ept', 'ex', 'it', 'ph', 'sc', 'x' => self::extractInlineCode($child, $unit),
                default => self::extractPgsText($child, $unit),
            };
        }

        return $text;
    }

    /**
     * @return list<string>
     */
    private static function splitWhitespace(string $value): array
    {
        $value = trim($value);

        if ($value === '') {
            return [];
        }

        $parts = preg_split('/\s+/', $value);

        return is_array($parts) ? $parts : [];
    }

    private static function extractText(DOMElement $element, DOMElement $unit): string
    {
        $text = '';

        for ($index = 0; $index < $element->childNodes->length; $index++) {
            $child = $element->childNodes->item($index);

            if ($child instanceof DOMText) {
                $text .= $child->nodeValue ?? '';

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $text .= match ($child->localName) {
                'cp' => self::extractCodePoint($child),
                'pc' => self::extractPairedCode($child, $unit),
                'bpt', 'bx', 'ec', 'ept', 'ex', 'it', 'ph', 'sc', 'x' => self::extractInlineCode($child, $unit),
                default => self::extractText($child, $unit),
            };
        }

        return $text;
    }

    private static function extractInlineCode(DOMElement $element, DOMElement $unit): string
    {
        foreach (['equiv-text', 'equiv'] as $attribute) {
            if ($element->hasAttribute($attribute)) {
                return $element->getAttribute($attribute);
            }
        }

        $content = self::extractText($element, $unit);

        if ($content !== '') {
            return $content;
        }

        $referenced = self::findReferencedData($element, $unit, 'dataRef');

        if ($referenced !== null) {
            return $referenced;
        }

        return $element->getAttribute('disp');
    }

    private static function extractPairedCode(DOMElement $element, DOMElement $unit): string
    {
        $start = self::findEquivalentOrReferencedData($element, $unit, 'equivStart', 'dataRefStart');
        $end = self::findEquivalentOrReferencedData($element, $unit, 'equivEnd', 'dataRefEnd');

        return ($start ?? '') . self::extractText($element, $unit) . ($end ?? '');
    }

    private static function findEquivalentOrReferencedData(
        DOMElement $element,
        DOMElement $unit,
        string $equivalentAttribute,
        string $referenceAttribute,
    ): ?string {
        if ($element->hasAttribute($equivalentAttribute)) {
            return $element->getAttribute($equivalentAttribute);
        }

        return self::findReferencedData($element, $unit, $referenceAttribute);
    }

    private static function findReferencedData(DOMElement $element, DOMElement $unit, string $attribute): ?string
    {
        if (! $element->hasAttribute($attribute)) {
            return null;
        }

        $reference = $element->getAttribute($attribute);

        for ($index = 0; $index < $unit->childNodes->length; $index++) {
            $originalData = $unit->childNodes->item($index);
            if (! $originalData instanceof DOMElement) {
                continue;
            }

            if ($originalData->localName !== 'originalData') {
                continue;
            }

            for ($dataIndex = 0; $dataIndex < $originalData->childNodes->length; $dataIndex++) {
                $data = $originalData->childNodes->item($dataIndex);

                if ($data instanceof DOMElement && $data->localName === 'data' && $data->getAttribute('id') === $reference) {
                    return self::extractText($data, $unit);
                }
            }
        }

        return null;
    }

    private static function extractCodePoint(DOMElement $element): string
    {
        $hex = $element->getAttribute('hex');

        if (preg_match('/\A[0-9a-f]{1,6}\z/i', $hex) !== 1) {
            return '';
        }

        $codePoint = hexdec($hex);

        if (! is_int($codePoint) || $codePoint > 0x10_FFFF || $codePoint >= 0xD800 && $codePoint <= 0xDFFF) {
            return '';
        }

        return (string) IntlChar::chr($codePoint);
    }

    private static function createXPath(DOMDocument $document, string $namespace): DOMXPath
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('xliff', $namespace);

        return $xpath;
    }

    private static function findChild(DOMXPath $xpath, DOMElement $parent, string $name): ?DOMElement
    {
        /** @var DOMNodeList|false $nodes */
        $nodes = $xpath->query("./xliff:{$name}", $parent);

        if (! $nodes instanceof DOMNodeList) {
            return null;
        }

        $node = $nodes->item(0);

        return $node instanceof DOMElement ? $node : null;
    }
}
