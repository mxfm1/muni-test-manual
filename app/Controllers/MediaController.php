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

        try {
            $media = $this->storage->store(
                $sectionId,
                $temporaryPath,
                basename((string) ($file['name'] ?? 'image')),
                $mimeType,
                (int) ($file['size'] ?? 0),
            );
            $this->mediaRepository->save($media);
        } catch (\InvalidArgumentException) {
            $this->respond($wantsJson, '/handbook/edit?media=invalid', 422, ['ok' => false, 'error' => 'invalid-image']);
        } catch (\Throwable) {
            if (isset($media)) {
                try {
                    $this->storage->delete($media);
                } catch (\Throwable) {
                    // Keep the original failure as the response outcome.
                }
            }

            $this->respond($wantsJson, '/handbook/edit?media=error', 500, ['ok' => false, 'error' => 'storage-failed']);
        }

        $this->respond($wantsJson, '/handbook/edit?media=success', 201, ['ok' => true, 'url' => $media->url]);
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
