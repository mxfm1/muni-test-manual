<?php

declare(strict_types=1);

namespace ManualMuni\Controllers;

use ManualMuni\Models\Media;
use ManualMuni\Models\Repositories\MediaRepository;
use ManualMuni\Models\Repositories\SectionRepository;
use ManualMuni\Services\ImageStorage;
use ManualMuni\Support\CsrfTokenManager;

final readonly class MediaController
{
    private const MAX_IMAGE_SIZE_IN_BYTES = 5_242_880;
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct(
        private ImageStorage $storage,
        private MediaRepository $mediaRepository,
        private SectionRepository $sectionRepository,
        private CsrfTokenManager $csrfTokenManager,
    ) {
    }

    public function store(int $sectionId): never
    {
        $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

        if (!$this->csrfTokenManager->isValid($_POST['csrf_token'] ?? null)) {
            $this->respond($wantsJson, '/handbook/edit?media=error', 400, ['ok' => false, 'error' => 'invalid-csrf']);
        }

        if (!$this->sectionRepository->exists($sectionId)) {
            $this->respond($wantsJson, '/handbook/edit?media=section-not-found', 404, ['ok' => false, 'error' => 'section-not-found']);
        }

        $file = $_FILES['image'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->respond($wantsJson, '/handbook/edit?media=error', 400, ['ok' => false, 'error' => 'upload-error']);
        }

        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        if ($temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            $this->respond($wantsJson, '/handbook/edit?media=error', 400, ['ok' => false, 'error' => 'invalid-upload']);
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        if ($mimeType === false) {
            $this->respond($wantsJson, '/handbook/edit?media=error', 400, ['ok' => false, 'error' => 'mime-detect-failed']);
        }

        $actualSize = filesize($temporaryPath);
        if ($actualSize === false || $actualSize === 0) {
            $this->respond($wantsJson, '/handbook/edit?media=error', 400, ['ok' => false, 'error' => 'upload-error']);
        }

        if ($actualSize > self::MAX_IMAGE_SIZE_IN_BYTES) {
            $this->respond($wantsJson, '/handbook/edit?media=invalid', 422, ['ok' => false, 'error' => 'invalid-size']);
        }

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            $this->respond($wantsJson, '/handbook/edit?media=invalid', 422, ['ok' => false, 'error' => 'invalid-mime']);
        }

        try {
            $media = $this->storage->store(
                $sectionId,
                $temporaryPath,
                basename((string) ($file['name'] ?? 'image')),
                $mimeType,
                $actualSize,
            );
        } catch (\InvalidArgumentException) {
            $this->respond($wantsJson, '/handbook/edit?media=invalid', 422, ['ok' => false, 'error' => 'invalid-image']);
        } catch (\Throwable $exception) {
            error_log(sprintf(
                'Media storage failed for section %d: %s: %s',
                $sectionId,
                $exception::class,
                $exception->getMessage(),
            ));

            $this->respond($wantsJson, '/handbook/edit?media=error', 500, ['ok' => false, 'error' => 'storage-unavailable']);
        }

        try {
            $assetId = $this->mediaRepository->save($media);
        } catch (\Throwable $exception) {
            error_log(sprintf(
                'Media database failed for section %d and storage key %s: %s: %s',
                $sectionId,
                $media->storageKey,
                $exception::class,
                $exception->getMessage(),
            ));

            try {
                $this->storage->delete($media);
            } catch (\Throwable $deleteException) {
                error_log(sprintf(
                    'Media rollback delete failed for storage key %s: %s: %s',
                    $media->storageKey,
                    $deleteException::class,
                    $deleteException->getMessage(),
                ));
            }

            $this->respond($wantsJson, '/handbook/edit?media=error', 500, ['ok' => false, 'error' => 'database-error']);
        }

        $this->respond($wantsJson, '/handbook/edit?media=success', 201, [
            'ok' => true,
            'assetId' => $assetId,
            'url' => $media->url,
        ]);
    }

    private function respond(bool $wantsJson, string $redirectUrl, int $jsonStatus, array $jsonBody): never
    {
        if ($wantsJson) {
            http_response_code($jsonStatus);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($jsonBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Location: ' . $redirectUrl, true, 303);
        exit;
    }
}
