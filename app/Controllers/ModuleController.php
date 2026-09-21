<?php

declare(strict_types=1);

namespace ManualMuni\Controllers;

use ManualMuni\Models\Repositories\ModuleRepository;
use ManualMuni\Models\Repositories\MediaRepository;
use ManualMuni\Services\ImageStorage;
use ManualMuni\Support\CsrfTokenManager;

final readonly class ModuleController
{
    public function __construct(
        private ModuleRepository $moduleRepository,
        private MediaRepository $mediaRepository,
        private ImageStorage $storage,
        private CsrfTokenManager $csrfTokenManager,
    ) {
    }

    public function create(): never
    {
        if (!$this->csrfTokenManager->isValid($_POST['csrf_token'] ?? null)) {
            $this->redirect('/handbook/edit?module=error');
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        if ($title === '' || mb_strlen($title) > 255) {
            $this->redirect('/handbook/edit?module=invalid');
        }

        $description = trim((string) ($_POST['description'] ?? ''));
        $description = $description === '' ? null : $description;
        if ($description !== null && mb_strlen($description) > 2000) {
            $this->redirect('/handbook/edit?module=desc-invalid');
        }

        try {
            $moduleId = $this->moduleRepository->create($title, $description);
        } catch (\Throwable) {
            $this->redirect('/handbook/edit?module=error');
        }

        $this->redirect('/handbook/edit?module=created&selected_module=' . $moduleId);
    }

    public function update(): never
    {
        if (!$this->csrfTokenManager->isValid($_POST['csrf_token'] ?? null)) {
            $this->redirect('/handbook/edit?module=error');
        }

        $moduleId = $this->moduleId();
        $title = trim((string) ($_POST['title'] ?? ''));
        if ($title === '' || mb_strlen($title) > 255) {
            $this->redirect('/handbook/edit?module=invalid&selected_module=' . $moduleId);
        }

        $description = trim((string) ($_POST['description'] ?? ''));
        $description = $description === '' ? null : $description;
        if ($description !== null && mb_strlen($description) > 2000) {
            $this->redirect('/handbook/edit?module=desc-invalid&selected_module=' . $moduleId);
        }

        try {
            if (!$this->moduleRepository->update($moduleId, $title, $description)) {
                $this->redirect('/handbook/edit?module=error');
            }
        } catch (\Throwable) {
            $this->redirect('/handbook/edit?module=error');
        }

        $this->redirect('/handbook/edit?module=updated&selected_module=' . $moduleId);
    }

    public function delete(): never
    {
        if (!$this->csrfTokenManager->isValid($_POST['csrf_token'] ?? null)) {
            $this->redirect('/handbook/edit?module=error');
        }

        $moduleId = $this->moduleId();

        try {
            $media = $this->mediaRepository->findByModuleId($moduleId);
            if (!$this->moduleRepository->delete($moduleId)) {
                $this->redirect('/handbook/edit?module=error');
            }

            foreach ($media as $item) {
                try {
                    $this->storage->delete($item);
                } catch (\Throwable) {
                    // The database cascade has already removed the media reference.
                }
            }
        } catch (\Throwable) {
            $this->redirect('/handbook/edit?module=error');
        }

        $this->redirect('/handbook/edit?module=deleted');
    }

    private function moduleId(): int
    {
        $value = $_POST['module_id'] ?? null;

        if (!is_string($value) || preg_match('/^[1-9][0-9]*$/', $value) !== 1) {
            $this->redirect('/handbook/edit?module=error');
        }

        return (int) $value;
    }

    private function redirect(string $location): never
    {
        header('Location: ' . $location, true, 303);
        exit;
    }
}
