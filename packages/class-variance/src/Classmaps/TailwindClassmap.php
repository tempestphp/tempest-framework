<?php

declare(strict_types=1);

namespace Tempest\ClassVariance\Classmaps;

/**
 * Default Tailwind CSS v3/v4 class group definitions and conflict rules.
 *
 * Ported from tailwind-merge (MIT) and verified against:
 * https://github.com/tales-from-a-dev/tailwind-merge-php/blob/main/src/Support/Config.php
 *
 * Matcher shapes (see Classmap for full documentation):
 *   string                       → exact class name
 *   ['prefix']                   → wildcard prefix (any suffix)
 *   ['prefix' => ['v1', 'v2']]  → constrained prefix (named suffixes only)
 *
 * Groups sharing a prefix use constrained matchers for the scale/size group and
 * wildcard matchers for the color group. Since iteration order determines priority,
 * the scale group must appear before the color group in the returned array.
 *
 * Known limitation: arbitrary values (e.g. border-[3px]) are not disambiguated
 * from color values — they fall through to the color group. Full disambiguation
 * requires validator callbacks, which can be added via extend().
 */
final class TailwindClassmap
{
    public static function default(): Classmap
    {
        return new Classmap(
            classGroups: self::classGroups(),
            conflictingClassGroups: self::conflictingClassGroups(),
        );
    }

    /** @return array<string, list<string|array<string, list<string>>|array{0: string}>> */
    private static function classGroups(): array
    {
        // Common scale values reused across groups
        $borderWidthScale = ['', '0', '2', '4', '8'];
        $tshirtScale = ['xs', 'sm', 'base', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl', '8xl', '9xl'];
        $fontSizeScale = ['xs', 'sm', 'base', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl', '8xl', '9xl'];
        $shadowScale = ['', 'sm', 'md', 'lg', 'xl', '2xl', 'inner', 'none'];

        return [
            // ── Layout ───────────────────────────────────────────────────────────

            'aspect' => [['aspect']],
            'container' => ['container'],
            'columns' => [['columns']],
            'break-after' => [['break-after' => ['auto', 'avoid', 'all', 'avoid-page', 'page', 'left', 'right', 'column']]],
            'break-before' => [['break-before' => ['auto', 'avoid', 'all', 'avoid-page', 'page', 'left', 'right', 'column']]],
            'break-inside' => [['break-inside' => ['auto', 'avoid', 'avoid-page', 'avoid-column']]],
            'box-decoration' => [['box-decoration' => ['clone', 'slice']]],
            'box' => [['box' => ['border', 'content']]],
            'display' => [
                'block',
                'inline-block',
                'inline',
                'flex',
                'inline-flex',
                'table',
                'inline-table',
                'table-caption',
                'table-cell',
                'table-column',
                'table-column-group',
                'table-footer-group',
                'table-header-group',
                'table-row-group',
                'table-row',
                'flow-root',
                'grid',
                'inline-grid',
                'contents',
                'list-item',
                'hidden',
            ],
            'float' => [['float' => ['start', 'end', 'right', 'left', 'none']]],
            'clear' => [['clear' => ['start', 'end', 'right', 'left', 'both', 'none']]],
            'isolation' => ['isolate', 'isolation-auto'],
            'object-fit' => [['object' => ['contain', 'cover', 'fill', 'none', 'scale-down']]],
            'object-position' => [['object' => ['bottom', 'center', 'left', 'left-bottom', 'left-top', 'right', 'right-bottom', 'right-top', 'top']]],
            'overflow' => [['overflow' => ['auto', 'hidden', 'clip', 'visible', 'scroll']]],
            'overflow-x' => [['overflow-x' => ['auto', 'hidden', 'clip', 'visible', 'scroll']]],
            'overflow-y' => [['overflow-y' => ['auto', 'hidden', 'clip', 'visible', 'scroll']]],
            'overscroll' => [['overscroll' => ['auto', 'contain', 'none']]],
            'overscroll-x' => [['overscroll-x' => ['auto', 'contain', 'none']]],
            'overscroll-y' => [['overscroll-y' => ['auto', 'contain', 'none']]],
            'position' => ['static', 'fixed', 'absolute', 'relative', 'sticky'],
            'inset' => [['inset']],
            'inset-x' => [['inset-x']],
            'inset-y' => [['inset-y']],
            'start' => [['start']],
            'end' => [['end']],
            'top' => [['top']],
            'right' => [['right']],
            'bottom' => [['bottom']],
            'left' => [['left']],
            'visibility' => ['visible', 'invisible', 'collapse'],
            'z' => [['z']],

            // ── Flexbox & Grid ────────────────────────────────────────────────────

            'basis' => [['basis']],
            'flex-direction' => [['flex' => ['row', 'row-reverse', 'col', 'col-reverse']]],
            'flex-wrap' => [['flex' => ['wrap', 'wrap-reverse', 'nowrap']]],
            'flex' => [['flex']],
            'grow' => [['grow']],
            'shrink' => [['shrink']],
            'order' => [['order']],
            'grid-cols' => [['grid-cols']],
            'grid-rows' => [['grid-rows']],
            'col-span' => [['col-span'], 'col-auto'],
            'col-start' => [['col-start']],
            'col-end' => [['col-end']],
            'row-span' => [['row-span'], 'row-auto'],
            'row-start' => [['row-start']],
            'row-end' => [['row-end']],
            'grid-flow' => [['grid-flow' => ['row', 'col', 'dense', 'row-dense', 'col-dense']]],
            'auto-cols' => [['auto-cols']],
            'auto-rows' => [['auto-rows']],
            'gap' => [['gap']],
            'gap-x' => [['gap-x']],
            'gap-y' => [['gap-y']],
            'justify-content' => [['justify' => ['normal', 'start', 'end', 'center', 'between', 'around', 'evenly', 'stretch']]],
            'justify-items' => [['justify-items' => ['start', 'end', 'center', 'stretch']]],
            'justify-self' => [['justify-self' => ['auto', 'start', 'end', 'center', 'stretch']]],
            'align-content' => [['content' => ['normal', 'center', 'start', 'end', 'between', 'around', 'evenly', 'baseline', 'stretch']]],
            'align-items' => [['items' => ['start', 'end', 'center', 'baseline', 'stretch']]],
            'align-self' => [['self' => ['auto', 'start', 'end', 'center', 'stretch', 'baseline']]],
            'place-content' => [['place-content' => ['center', 'start', 'end', 'between', 'around', 'evenly', 'baseline', 'stretch']]],
            'place-items' => [['place-items' => ['start', 'end', 'center', 'baseline', 'stretch']]],
            'place-self' => [['place-self' => ['auto', 'start', 'end', 'center', 'stretch']]],

            // ── Spacing ───────────────────────────────────────────────────────────

            'p' => [['p']],
            'px' => [['px']],
            'py' => [['py']],
            'ps' => [['ps']],
            'pe' => [['pe']],
            'pt' => [['pt']],
            'pr' => [['pr']],
            'pb' => [['pb']],
            'pl' => [['pl']],
            'm' => [['m']],
            'mx' => [['mx']],
            'my' => [['my']],
            'ms' => [['ms']],
            'me' => [['me']],
            'mt' => [['mt']],
            'mr' => [['mr']],
            'mb' => [['mb']],
            'ml' => [['ml']],
            'space-x' => [['space-x']],
            'space-y' => [['space-y']],
            'space-x-reverse' => ['space-x-reverse'],
            'space-y-reverse' => ['space-y-reverse'],

            // ── Sizing ────────────────────────────────────────────────────────────

            'w' => [['w']],
            'min-w' => [['min-w']],
            'max-w' => [['max-w']],
            'h' => [['h']],
            'min-h' => [['min-h']],
            'max-h' => [['max-h']],
            'size' => [['size']],

            // ── Typography ────────────────────────────────────────────────────────
            // font-size uses constrained 'text' prefix; text-color uses wildcard.
            // Iteration order ensures font-size is matched before text-color.

            'font-size' => [['text' => $fontSizeScale]],
            'font-smoothing' => ['antialiased', 'subpixel-antialiased'],
            'font-style' => ['italic', 'not-italic'],
            'font-weight' => [['font' => ['thin', 'extralight', 'light', 'normal', 'medium', 'semibold', 'bold', 'extrabold', 'black']]],
            'font-family' => [['font']],
            'font-variant-numeric' => [
                'normal-nums',
                'ordinal',
                'slashed-zero',
                'lining-nums',
                'oldstyle-nums',
                'proportional-nums',
                'tabular-nums',
                'diagonal-fractions',
                'stacked-fractions',
            ],
            'tracking' => [['tracking' => ['tighter', 'tight', 'normal', 'wide', 'wider', 'widest']]],
            'line-clamp' => [['line-clamp']],
            'leading' => [['leading' => ['none', 'tight', 'snug', 'normal', 'relaxed', 'loose']]],
            'list-image' => [['list-image']],
            'list-style-position' => ['list-inside', 'list-outside'],
            'list-style-type' => [['list' => ['none', 'disc', 'decimal']]],
            'text-align' => [['text' => ['left', 'center', 'right', 'justify', 'start', 'end']]],
            'text-color' => [['text']], // wildcard — catches anything not matched above
            'text-decoration' => ['underline', 'overline', 'line-through', 'no-underline'],
            'text-decoration-color' => [['decoration']],
            'text-decoration-style' => [['decoration' => ['solid', 'double', 'dotted', 'dashed', 'wavy']]],
            'text-decoration-thickness' => [['underline-offset']],
            'text-transform' => ['uppercase', 'lowercase', 'capitalize', 'normal-case'],
            'text-overflow' => ['truncate', 'text-ellipsis', 'text-clip'],
            'text-wrap' => [['text' => ['wrap', 'nowrap', 'balance', 'pretty']]],
            'indent' => [['indent']],
            'vertical-align' => [['align' => ['baseline', 'top', 'middle', 'bottom', 'text-top', 'text-bottom', 'sub', 'super']]],
            'whitespace' => [['whitespace' => ['normal', 'nowrap', 'pre', 'pre-line', 'pre-wrap', 'break-spaces']]],
            'break-words' => [['break' => ['normal', 'words', 'all', 'keep']]],
            'hyphens' => [['hyphens' => ['none', 'manual', 'auto']]],
            'content' => [['content']],

            // ── Backgrounds ───────────────────────────────────────────────────────

            'bg-attach' => [['bg' => ['fixed', 'local', 'scroll']]],
            'bg-clip' => [['bg-clip' => ['border', 'padding', 'content', 'text']]],
            'bg-color' => [['bg']], // wildcard — catches bg-{color}
            'bg-origin' => [['bg-origin' => ['border', 'padding', 'content']]],
            'bg-position' => [['bg' => ['bottom', 'center', 'left', 'left-bottom', 'left-top', 'right', 'right-bottom', 'right-top', 'top']]],
            'bg-repeat' => [['bg' => ['repeat', 'no-repeat', 'repeat-x', 'repeat-y', 'repeat-round', 'repeat-space']]],
            'bg-size' => [['bg' => ['auto', 'cover', 'contain']]],
            'bg-image' => [
                ['bg' => ['none', 'gradient-to-t', 'gradient-to-tr', 'gradient-to-r', 'gradient-to-br', 'gradient-to-b', 'gradient-to-bl', 'gradient-to-l', 'gradient-to-tl']],
            ],
            'gradient-from' => [['from']],
            'gradient-via' => [['via']],
            'gradient-to' => [['to']],

            // ── Borders ───────────────────────────────────────────────────────────
            // border-w uses constrained suffix list; border-color uses wildcard.

            'rounded' => [['rounded']],
            'rounded-s' => [['rounded-s']],
            'rounded-e' => [['rounded-e']],
            'rounded-t' => [['rounded-t']],
            'rounded-r' => [['rounded-r']],
            'rounded-b' => [['rounded-b']],
            'rounded-l' => [['rounded-l']],
            'rounded-ss' => [['rounded-ss']],
            'rounded-se' => [['rounded-se']],
            'rounded-ee' => [['rounded-ee']],
            'rounded-es' => [['rounded-es']],
            'rounded-tl' => [['rounded-tl']],
            'rounded-tr' => [['rounded-tr']],
            'rounded-br' => [['rounded-br']],
            'rounded-bl' => [['rounded-bl']],
            'border-w' => [['border' => $borderWidthScale]], // constrained: border, border-0, border-2, border-4, border-8
            'border-color' => [['border']], // wildcard: border-{color/anything-else}
            'border-w-x' => [['border-x' => $borderWidthScale]],
            'border-color-x' => [['border-x']],
            'border-w-y' => [['border-y' => $borderWidthScale]],
            'border-color-y' => [['border-y']],
            'border-w-s' => [['border-s' => $borderWidthScale]],
            'border-color-s' => [['border-s']],
            'border-w-e' => [['border-e' => $borderWidthScale]],
            'border-color-e' => [['border-e']],
            'border-w-t' => [['border-t' => $borderWidthScale]],
            'border-color-t' => [['border-t']],
            'border-w-r' => [['border-r' => $borderWidthScale]],
            'border-color-r' => [['border-r']],
            'border-w-b' => [['border-b' => $borderWidthScale]],
            'border-color-b' => [['border-b']],
            'border-w-l' => [['border-l' => $borderWidthScale]],
            'border-color-l' => [['border-l']],
            'border-style' => [['border' => ['solid', 'dashed', 'dotted', 'double', 'hidden', 'none']]],
            'divide-x' => [['divide-x' => $borderWidthScale]],
            'divide-color-x' => [['divide-x']],
            'divide-y' => [['divide-y' => $borderWidthScale]],
            'divide-color-y' => [['divide-y']],
            'divide-color' => [['divide']],
            'divide-style' => [['divide' => ['solid', 'dashed', 'dotted', 'double', 'none']]],
            'outline-w' => [['outline' => $borderWidthScale]],
            'outline-color' => [['outline']],
            'outline-style' => [['outline' => ['none', 'solid', 'dashed', 'dotted', 'double']]],
            'outline-offset' => [['outline-offset']],
            'ring-w' => [['ring' => ['', '0', '1', '2', '4', '8']]], // constrained: ring, ring-0, ring-1, ring-2, ring-4, ring-8
            'ring-w-inset' => ['ring-inset'],
            'ring-color' => [['ring']], // wildcard: ring-{color}
            'ring-offset-w' => [['ring-offset' => $borderWidthScale]],
            'ring-offset-color' => [['ring-offset']],

            // ── Effects ───────────────────────────────────────────────────────────
            // shadow uses constrained suffix list; shadow-color uses wildcard.

            'shadow' => [['shadow' => $shadowScale]], // shadow, shadow-sm, shadow-md, shadow-lg, shadow-xl, shadow-2xl, shadow-inner, shadow-none
            'shadow-color' => [['shadow']], // wildcard: shadow-{color}
            'inset-shadow' => [['inset-shadow' => $shadowScale]],
            'inset-shadow-color' => [['inset-shadow']],
            'opacity' => [['opacity']],
            'mix-blend' => [['mix-blend']],
            'bg-blend' => [['bg-blend']],

            // ── Filters ───────────────────────────────────────────────────────────

            'filter' => ['filter', 'filter-none'],
            'blur' => [['blur' => $tshirtScale]],
            'brightness' => [['brightness']],
            'contrast' => [['contrast']],
            'drop-shadow' => [['drop-shadow' => $shadowScale]],
            'drop-shadow-color' => [['drop-shadow']],
            'grayscale' => [['grayscale' => ['', '0']]],
            'hue-rotate' => [['hue-rotate']],
            'invert' => [['invert' => ['', '0']]],
            'saturate' => [['saturate']],
            'sepia' => [['sepia' => ['', '0']]],
            'backdrop-filter' => ['backdrop-filter', 'backdrop-filter-none'],
            'backdrop-blur' => [['backdrop-blur' => $tshirtScale]],
            'backdrop-brightness' => [['backdrop-brightness']],
            'backdrop-contrast' => [['backdrop-contrast']],
            'backdrop-grayscale' => [['backdrop-grayscale' => ['', '0']]],
            'backdrop-hue-rotate' => [['backdrop-hue-rotate']],
            'backdrop-invert' => [['backdrop-invert' => ['', '0']]],
            'backdrop-opacity' => [['backdrop-opacity']],
            'backdrop-saturate' => [['backdrop-saturate']],
            'backdrop-sepia' => [['backdrop-sepia' => ['', '0']]],

            // ── Transforms ────────────────────────────────────────────────────────

            'transform' => [['transform' => ['', 'cpu', 'gpu', 'none']]],
            'scale' => [['scale']],
            'scale-x' => [['scale-x']],
            'scale-y' => [['scale-y']],
            'rotate' => [['rotate']],
            'translate-x' => [['translate-x']],
            'translate-y' => [['translate-y']],
            'skew-x' => [['skew-x']],
            'skew-y' => [['skew-y']],
            'transform-origin' => [['origin']],
            'perspective' => [['perspective' => ['dramatic', 'near', 'normal', 'midrange', 'distant', 'none']]],
            'perspective-origin' => [['perspective-origin']],
            'backface' => [['backface' => ['hidden', 'visible']]],

            // ── Transitions & Animation ───────────────────────────────────────────

            'transition' => [['transition']],
            'duration' => [['duration']],
            'ease' => [['ease' => ['linear', 'in', 'out', 'in-out']]],
            'delay' => [['delay']],
            'animate' => [['animate' => ['none', 'spin', 'ping', 'pulse', 'bounce']]],
            'will-change' => [['will-change']],

            // ── Interactivity ─────────────────────────────────────────────────────

            'appearance' => [['appearance' => ['none', 'auto']]],
            'cursor' => [['cursor']],
            'caret-color' => [['caret']],
            'pointer-events' => [['pointer-events' => ['none', 'auto']]],
            'resize' => ['resize-none', 'resize-y', 'resize-x', 'resize'],
            'scroll-behavior' => [['scroll' => ['auto', 'smooth']]],
            'scroll-m' => [['scroll-m']],
            'scroll-mx' => [['scroll-mx']],
            'scroll-my' => [['scroll-my']],
            'scroll-ms' => [['scroll-ms']],
            'scroll-me' => [['scroll-me']],
            'scroll-mt' => [['scroll-mt']],
            'scroll-mr' => [['scroll-mr']],
            'scroll-mb' => [['scroll-mb']],
            'scroll-ml' => [['scroll-ml']],
            'scroll-p' => [['scroll-p']],
            'scroll-px' => [['scroll-px']],
            'scroll-py' => [['scroll-py']],
            'scroll-ps' => [['scroll-ps']],
            'scroll-pe' => [['scroll-pe']],
            'scroll-pt' => [['scroll-pt']],
            'scroll-pr' => [['scroll-pr']],
            'scroll-pb' => [['scroll-pb']],
            'scroll-pl' => [['scroll-pl']],
            'snap-align' => [['snap' => ['start', 'end', 'center', 'align-none']]],
            'snap-stop' => [['snap' => ['normal', 'always']]],
            'snap-type' => [['snap' => ['none', 'x', 'y', 'both']]],
            'snap-strictness' => [['snap' => ['mandatory', 'proximity']]],
            'touch' => [['touch']],
            'select' => [['select' => ['none', 'text', 'all', 'auto']]],

            // ── SVG ───────────────────────────────────────────────────────────────

            'fill' => [['fill']],
            'stroke-w' => [['stroke' => ['', '0', '1', '2']]],
            'stroke-color' => [['stroke']],

            // ── Accessibility ─────────────────────────────────────────────────────

            'sr' => ['sr-only', 'not-sr-only'],

            // ── Table ─────────────────────────────────────────────────────────────

            'border-collapse' => [['border' => ['collapse', 'separate']]],
            'border-spacing' => [['border-spacing']],
            'border-spacing-x' => [['border-spacing-x']],
            'border-spacing-y' => [['border-spacing-y']],
            'caption-side' => [['caption' => ['top', 'bottom']]],
            'table-layout' => [['table' => ['auto', 'fixed']]],
        ];
    }

    /** @return array<string, list<string>> */
    private static function conflictingClassGroups(): array
    {
        return [
            'p' => ['px', 'py', 'ps', 'pe', 'pt', 'pr', 'pb', 'pl'],
            'px' => ['ps', 'pe'],
            'py' => ['pt', 'pb'],
            'm' => ['mx', 'my', 'ms', 'me', 'mt', 'mr', 'mb', 'ml'],
            'mx' => ['ms', 'me'],
            'my' => ['mt', 'mb'],
            'inset' => ['inset-x', 'inset-y', 'start', 'end', 'top', 'right', 'bottom', 'left'],
            'inset-x' => ['start', 'end'],
            'inset-y' => ['top', 'bottom'],
            'gap' => ['gap-x', 'gap-y'],
            'overflow' => ['overflow-x', 'overflow-y'],
            'overscroll' => ['overscroll-x', 'overscroll-y'],
            'rounded' => [
                'rounded-s',
                'rounded-e',
                'rounded-t',
                'rounded-r',
                'rounded-b',
                'rounded-l',
                'rounded-ss',
                'rounded-se',
                'rounded-ee',
                'rounded-es',
                'rounded-tl',
                'rounded-tr',
                'rounded-br',
                'rounded-bl',
            ],
            'rounded-t' => ['rounded-tl', 'rounded-tr'],
            'rounded-r' => ['rounded-tr', 'rounded-br'],
            'rounded-b' => ['rounded-br', 'rounded-bl'],
            'rounded-l' => ['rounded-tl', 'rounded-bl'],
            'rounded-s' => ['rounded-ss', 'rounded-es'],
            'rounded-e' => ['rounded-se', 'rounded-ee'],
            'border-w' => ['border-w-x', 'border-w-y', 'border-w-s', 'border-w-e', 'border-w-t', 'border-w-r', 'border-w-b', 'border-w-l'],
            'border-w-x' => ['border-w-s', 'border-w-e'],
            'border-w-y' => ['border-w-t', 'border-w-b'],
            'border-color' => ['border-color-x', 'border-color-y', 'border-color-s', 'border-color-e', 'border-color-t', 'border-color-r', 'border-color-b', 'border-color-l'],
            'border-color-x' => ['border-color-s', 'border-color-e'],
            'border-color-y' => ['border-color-t', 'border-color-b'],
            'scale' => ['scale-x', 'scale-y'],
            'font-size' => ['leading'],
            'scroll-m' => ['scroll-mx', 'scroll-my', 'scroll-ms', 'scroll-me', 'scroll-mt', 'scroll-mr', 'scroll-mb', 'scroll-ml'],
            'scroll-mx' => ['scroll-ms', 'scroll-me'],
            'scroll-my' => ['scroll-mt', 'scroll-mb'],
            'scroll-p' => ['scroll-px', 'scroll-py', 'scroll-ps', 'scroll-pe', 'scroll-pt', 'scroll-pr', 'scroll-pb', 'scroll-pl'],
            'scroll-px' => ['scroll-ps', 'scroll-pe'],
            'scroll-py' => ['scroll-pt', 'scroll-pb'],
        ];
    }
}
