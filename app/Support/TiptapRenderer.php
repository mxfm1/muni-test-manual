<?php

declare(strict_types=1);

namespace ManualMuni\Support;

/**
 * Renderiza documentos JSON generados por Tiptap (StarterKit + Link + Image +
 * TextStyle + FontFamily) a HTML semántico, para vistas públicas donde el
 * contenido debe quedar renderizado en el servidor. Todo el texto se escapa;
 * los nodos desconocidos se degradan a su texto plano.
 */
final readonly class TiptapRenderer
{
    public static function toHtml(string $json): string
    {
        try {
            $document = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $document = null;
        }

        if (!is_array($document) || ($document['type'] ?? '') !== 'doc') {
            $text = TiptapDocument::toPlainText($json, 1000);

            return $text === ''
                ? '<p class="topic-content-empty"></p>'
                : '<p>' . self::escape($text) . '</p>';
        }

        return self::renderChildren($document);
    }

    private static function renderChildren(array $parent): string
    {
        $html = '';

        foreach ($parent['content'] ?? [] as $child) {
            if (!is_array($child)) {
                continue;
            }
            $html .= self::renderNode($child);
        }

        return $html;
    }

    private static function renderNode(array $node): string
    {
        $type = $node['type'] ?? '';

        return match ($type) {
            'paragraph' => '<p>' . self::renderChildren($node) . '</p>',
            'heading' => self::renderHeading($node),
            'bulletList' => '<ul>' . self::renderChildren($node) . '</ul>',
            'orderedList' => self::renderOrderedList($node),
            'listItem' => '<li>' . self::renderChildren($node) . '</li>',
            'blockquote' => '<blockquote>' . self::renderChildren($node) . '</blockquote>',
            'codeBlock' => self::renderCodeBlock($node),
            'horizontalRule' => '<hr>',
            'hardBreak' => '<br>',
            'image' => self::renderImage($node),
            'text' => self::renderText($node),
            default => self::renderChildren($node),
        };
    }

    private static function renderHeading(array $node): string
    {
        $level = (int) ($node['attrs']['level'] ?? 2);
        $level = min(max($level, 1), 6);

        return '<h' . $level . '>' . self::renderChildren($node) . '</h' . $level . '>';
    }

    private static function renderOrderedList(array $node): string
    {
        $start = (int) ($node['attrs']['start'] ?? 1);
        $startAttr = $start > 1 ? ' start="' . $start . '"' : '';

        return '<ol' . $startAttr . '>' . self::renderChildren($node) . '</ol>';
    }

    private static function renderCodeBlock(array $node): string
    {
        $text = self::renderChildren($node);

        return '<pre><code>' . $text . '</code></pre>';
    }

    private static function renderImage(array $node): string
    {
        $attrs = $node['attrs'] ?? [];
        $src = is_string($attrs['src'] ?? null) ? htmlspecialchars($attrs['src'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
        $alt = is_string($attrs['alt'] ?? null) ? htmlspecialchars($attrs['alt'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';

        if ($src === '') {
            return '';
        }

        return $alt === ''
            ? '<img src="' . $src . '" alt="" loading="lazy">'
            : '<img src="' . $src . '" alt="' . $alt . '" loading="lazy">';
    }

    private static function renderText(array $node): string
    {
        $text = isset($node['text']) ? (string) $node['text'] : '';
        $escaped = self::escape($text);
        $marks = is_array($node['marks'] ?? null) ? $node['marks'] : [];

        return self::applyMarks($escaped, $marks);
    }

    /** @param list<array{type?: string, attrs?: array}> $marks */
    private static function applyMarks(string $html, array $marks): string
    {
        $linkHref = null;

        foreach ($marks as $mark) {
            $markType = $mark['type'] ?? '';
            $attrs = is_array($mark['attrs'] ?? null) ? $mark['attrs'] : [];

            if ($markType === 'link') {
                $href = is_string($attrs['href'] ?? null) ? $attrs['href'] : null;
                if ($href !== null && $href !== '') {
                    $linkHref = $href;
                }
                continue;
            }

            $html = match ($markType) {
                'bold' => '<strong>' . $html . '</strong>',
                'italic' => '<em>' . $html . '</em>',
                'underline' => '<u>' . $html . '</u>',
                'strike' => '<s>' . $html . '</s>',
                'code' => '<code>' . $html . '</code>',
                'textStyle' => self::applyTextStyle($html, $attrs),
                default => $html,
            };
        }

        if ($linkHref !== null) {
            $safeHref = htmlspecialchars($linkHref, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html = '<a href="' . $safeHref . '" target="_blank" rel="noopener noreferrer">' . $html . '</a>';
        }

        return $html;
    }

    /** @param array $attrs */
    private static function applyTextStyle(string $html, array $attrs): string
    {
        $fontFamily = $attrs['fontFamily'] ?? null;
        $color = $attrs['color'] ?? null;

        if (!is_string($fontFamily) && !is_string($color)) {
            return $html;
        }

        $styles = [];
        if (is_string($fontFamily) && $fontFamily !== '') {
            $styles[] = 'font-family:' . htmlspecialchars($fontFamily, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        if (is_string($color) && $color !== '') {
            $styles[] = 'color:' . htmlspecialchars($color, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        return '<span style="' . implode(';', $styles) . '">' . $html . '</span>';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}