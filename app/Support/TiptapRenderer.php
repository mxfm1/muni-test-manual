<?php

declare(strict_types=1);

namespace ManualMuni\Support;

use ManualMuni\Services\ImageUrlResolver;

/**
 * Renderiza documentos JSON generados por Tiptap (StarterKit + Link + Image +
 * TextStyle + FontFamily) a HTML semántico, para vistas públicas donde el
 * contenido debe quedar renderizado en el servidor. Todo el texto se escapa;
 * los nodos desconocidos se degradan a su texto plano.
 */
final readonly class TiptapRenderer
{
    public function __construct(private ?ImageUrlResolver $imageUrlResolver = null)
    {
    }

    public function toHtml(string $json): string
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

        return $this->renderChildren($document);
    }

    private function renderChildren(array $parent): string
    {
        $html = '';

        foreach ($parent['content'] ?? [] as $child) {
            if (!is_array($child)) {
                continue;
            }
            $html .= $this->renderNode($child);
        }

        return $html;
    }

    private function renderNode(array $node): string
    {
        $type = $node['type'] ?? '';

        return match ($type) {
            'paragraph' => '<p>' . $this->renderChildren($node) . '</p>',
            'heading' => $this->renderHeading($node),
            'bulletList' => '<ul>' . $this->renderChildren($node) . '</ul>',
            'orderedList' => $this->renderOrderedList($node),
            'listItem' => '<li>' . $this->renderChildren($node) . '</li>',
            'blockquote' => '<blockquote>' . $this->renderChildren($node) . '</blockquote>',
            'codeBlock' => $this->renderCodeBlock($node),
            'horizontalRule' => '<hr>',
            'hardBreak' => '<br>',
            'image' => $this->renderImage($node),
            'text' => $this->renderText($node),
            default => $this->renderChildren($node),
        };
    }

    private function renderHeading(array $node): string
    {
        $level = (int) ($node['attrs']['level'] ?? 2);
        $level = min(max($level, 1), 6);

        return '<h' . $level . '>' . $this->renderChildren($node) . '</h' . $level . '>';
    }

    private function renderOrderedList(array $node): string
    {
        $start = (int) ($node['attrs']['start'] ?? 1);
        $startAttr = $start > 1 ? ' start="' . $start . '"' : '';

        return '<ol' . $startAttr . '>' . $this->renderChildren($node) . '</ol>';
    }

    private function renderCodeBlock(array $node): string
    {
        $text = $this->renderChildren($node);

        return '<pre><code>' . $text . '</code></pre>';
    }

    private function renderImage(array $node): string
    {
        $attrs = $node['attrs'] ?? [];
        $assetId = filter_var($attrs['assetId'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $resolvedSrc = $assetId !== false && $this->imageUrlResolver !== null
            ? $this->imageUrlResolver->resolve($assetId)
            : null;
        $rawSrc = $resolvedSrc ?? (is_string($attrs['src'] ?? null) ? self::normalizeMediaUrl($attrs['src']) : '');
        $src = htmlspecialchars($rawSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $alt = is_string($attrs['alt'] ?? null) ? htmlspecialchars($attrs['alt'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';

        if ($src === '') {
            return '';
        }

        return $alt === ''
            ? '<img src="' . $src . '" alt="" loading="lazy">'
            : '<img src="' . $src . '" alt="' . $alt . '" loading="lazy">';
    }

    private static function normalizeMediaUrl(string $src): string
    {
        if (preg_match('#^/(?:public/)?uploads/media/(.+)$#', $src, $matches) !== 1) {
            return $src;
        }

        return PublicPath::for('uploads/media') . '/' . $matches[1];
    }

    private function renderText(array $node): string
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
