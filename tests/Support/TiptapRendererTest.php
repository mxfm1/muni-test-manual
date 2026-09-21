<?php

declare(strict_types=1);

namespace ManualMuni\Tests\Support;

use ManualMuni\Support\TiptapRenderer;
use PHPUnit\Framework\TestCase;

final class TiptapRendererTest extends TestCase
{
    public function testRendersParagraph(): void
    {
        $json = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Hola mundo"}]}]}';

        self::assertSame('<p>Hola mundo</p>', TiptapRenderer::toHtml($json));
    }

    public function testRendersHeadingWithLevel(): void
    {
        $json = '{"type":"doc","content":[{"type":"heading","attrs":{"level":2},"content":[{"type":"text","text":"Título"}]}]}';

        self::assertSame('<h2>Título</h2>', TiptapRenderer::toHtml($json));
    }

    public function testRendersLists(): void
    {
        $json = '{"type":"doc","content":[{"type":"bulletList","content":[{"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Uno"}]}]}]},{"type":"orderedList","attrs":{"start":3},"content":[{"type":"listItem","content":[{"type":"paragraph","content":[{"type":"text","text":"Tres"}]}]}]}]}';

        self::assertSame(
            '<ul><li><p>Uno</p></li></ul><ol start="3"><li><p>Tres</p></li></ol>',
            TiptapRenderer::toHtml($json),
        );
    }

    public function testRendersTextMarks(): void
    {
        $json = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","marks":[{"type":"bold"}],"text":"negrita"},{"type":"text","marks":[{"type":"italic"}],"text":"cursiva"},{"type":"text","marks":[{"type":"link","attrs":{"href":"https://mun.cl"}}],"text":"enlace"}]}]}';

        self::assertSame(
            '<p><strong>negrita</strong><em>cursiva</em><a href="https://mun.cl" target="_blank" rel="noopener noreferrer">enlace</a></p>',
            TiptapRenderer::toHtml($json),
        );
    }

    public function testRendersImage(): void
    {
        $json = '{"type":"doc","content":[{"type":"image","attrs":{"src":"/uploads/media/foto.jpg","alt":"Foto"}}]}';

        self::assertSame('<img src="/uploads/media/foto.jpg" alt="Foto" loading="lazy">', TiptapRenderer::toHtml($json));
    }

    public function testRendersCodeBlockAndHorizontalRule(): void
    {
        $json = '{"type":"doc","content":[{"type":"codeBlock","content":[{"type":"text","text":"SELECT 1"}]},{"type":"horizontalRule"}]}';

        self::assertSame('<pre><code>SELECT 1</code></pre><hr>', TiptapRenderer::toHtml($json));
    }

    public function testEscapesUserContent(): void
    {
        $json = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"<script>alert(1)</script>"}]}]}';

        self::assertSame('<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>', TiptapRenderer::toHtml($json));
    }

    public function testEscapesImageAttrs(): void
    {
        $json = '{"type":"doc","content":[{"type":"image","attrs":{"src":"x\" onerror=\"alert(1)","alt":"a&b"}}]}';

        self::assertSame('<img src="x&quot; onerror=&quot;alert(1)" alt="a&amp;b" loading="lazy">', TiptapRenderer::toHtml($json));
    }

    public function testInvalidJsonFallsBackToPlainParagraph(): void
    {
        self::assertSame('<p>&quot;no es json&quot;</p>', TiptapRenderer::toHtml('"no es json"'));
    }

    public function testUnknownNodeFallsBackToItsText(): void
    {
        $json = '{"type":"doc","content":[{"type":"misterioso","content":[{"type":"text","text":"caido"}]}]}';

        self::assertSame('caido', TiptapRenderer::toHtml($json));
    }

    public function testTextStyleFontFamily(): void
    {
        $json = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","marks":[{"type":"textStyle","attrs":{"fontFamily":"Arial"}}],"text":"fuente"}]}]}';

        self::assertSame('<p><span style="font-family:Arial">fuente</span></p>', TiptapRenderer::toHtml($json));
    }
}