<?php

declare(strict_types=1);

namespace ManualMuni\Tests\Services;

use ManualMuni\Services\LocalImageStorage;
use PHPUnit\Framework\TestCase;

final class LocalImageStorageTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/manual-muni-media-test-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($this->directory);
    }

    public function testStoreAndReadRoundTrip(): void
    {
        $source = $this->directory . '-source.png';
        $image = imagecreatetruecolor(120, 80);
        imagepng($image, $source);
        imagedestroy($image);

        $storage = new LocalImageStorage($this->directory, '/uploads/media');
        $media = $storage->store(1, $source, 'foto.png', 'image/png', filesize($source));

        $stored = $storage->read($media->storageKey);

        self::assertNotNull($stored, 'Debe poder leer el objeto recién guardado.');
        self::assertSame($media->mimeType, $stored->mimeType, 'El MIME leído debe coincidir con el guardado.');
        self::assertSame($media->sizeInBytes, strlen($stored->bytes), 'El tamaño leído debe coincidir con el codificado.');

        @unlink($source);
    }

    public function testReadReturnsNullForMissingKey(): void
    {
        $storage = new LocalImageStorage($this->directory, '/uploads/media');

        self::assertNull($storage->read('media/no-existe.webp'));
    }
}