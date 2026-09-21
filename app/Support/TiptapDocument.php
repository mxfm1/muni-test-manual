<?php

declare(strict_types=1);

namespace ManualMuni\Support;

final readonly class TiptapDocument
{
    /**
     * Extrae texto plano de un documento JSON generado por Tiptap,
     * recorriendo recursivamente la estructura de nodos. Se usa como
     * excerpt en resultados de búsqueda donde el contenido original es
     * JSON rich-text.
     */
    public static function toPlainText(string $json, int $maxLength = 140): string
    {
        try {
            $document = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $document = null;
        }

        if (!is_array($document) || ($document['type'] ?? '') !== 'doc') {
            return mb_strimwidth(trim($json), 0, $maxLength, '…');
        }

        $text = self::extractNode($document);
        $text = preg_replace('/\s+/', ' ', $text);

        if ($text === false || $text === '') {
            return '';
        }

        return mb_strimwidth($text, 0, $maxLength, '…');
    }

    private static function extractNode(array $node): string
    {
        $parts = [];

        foreach ($node['content'] ?? [] as $child) {
            if (isset($child['type']) && $child['type'] === 'text' && isset($child['text'])) {
                $parts[] = $child['text'];
            } else {
                $parts[] = self::extractNode($child);
            }
        }

        return implode(' ', array_filter($parts, static fn (string $p): bool => $p !== ''));
    }
}
