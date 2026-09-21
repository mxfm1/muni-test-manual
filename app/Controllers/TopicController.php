<?php

declare(strict_types=1);

namespace ManualMuni\Controllers;

use ManualMuni\Models\Repositories\ModuleRepository;
use ManualMuni\Models\Repositories\MediaRepository;
use ManualMuni\Models\Repositories\TopicRepository;
use ManualMuni\Services\ImageStorage;
use ManualMuni\Support\CsrfTokenManager;

final readonly class TopicController
{
    public function __construct(
        private TopicRepository $topicRepository,
        private ModuleRepository $moduleRepository,
        private MediaRepository $mediaRepository,
        private ImageStorage $storage,
        private CsrfTokenManager $csrfTokenManager,
    ) {
    }

    public function create(): never
    {
        if (!$this->csrfTokenManager->isValid($_POST['csrf_token'] ?? null)) {
            $this->redirect('/handbook/edit?topic=error');
        }

        $moduleId = $this->moduleId();
        $title = trim((string) ($_POST['title'] ?? ''));
        if ($title === '' || mb_strlen($title) > 255) {
            $this->redirect('/handbook/edit?topic=invalid&selected_module=' . $moduleId);
        }

        try {
            if (!$this->moduleRepository->exists($moduleId)) {
                $this->redirect('/handbook/edit?topic=error');
            }

            $topicId = $this->topicRepository->create($moduleId, $title);
        } catch (\Throwable) {
            $this->redirect('/handbook/edit?topic=error');
        }

        $this->redirect('/handbook/edit?topic=created&selected_module=' . $moduleId . '&selected_topic=' . $topicId);
    }

    public function update(): never
    {
        if (!$this->csrfTokenManager->isValid($_POST['csrf_token'] ?? null)) {
            $this->redirect('/handbook/edit?topic=error');
        }

        $topicId = $this->topicId();
        $moduleId = $this->topicRepository->findModuleId($topicId);
        if ($moduleId === null) {
            $this->redirect('/handbook/edit?topic=error');
        }

        $title = $this->title($moduleId, $topicId);

        try {
            if (!$this->topicRepository->updateTitle($topicId, $title)) {
                $this->redirect('/handbook/edit?topic=error&selected_module=' . $moduleId);
            }
        } catch (\Throwable) {
            $this->redirect('/handbook/edit?topic=error&selected_module=' . $moduleId);
        }

        $this->redirect('/handbook/edit?topic=updated&selected_module=' . $moduleId . '&selected_topic=' . $topicId);
    }

    public function delete(): never
    {
        if (!$this->csrfTokenManager->isValid($_POST['csrf_token'] ?? null)) {
            $this->redirect('/handbook/edit?topic=error');
        }

        $topicId = $this->topicId();
        $moduleId = $this->topicRepository->findModuleId($topicId);
        if ($moduleId === null) {
            $this->redirect('/handbook/edit?topic=error');
        }

        try {
            $media = $this->mediaRepository->findByTopicId($topicId);
            if (!$this->topicRepository->delete($topicId)) {
                $this->redirect('/handbook/edit?topic=error&selected_module=' . $moduleId);
            }

            foreach ($media as $item) {
                try {
                    $this->storage->delete($item);
                } catch (\Throwable) {
                    // The database cascade has already removed the media reference.
                }
            }
        } catch (\Throwable) {
            $this->redirect('/handbook/edit?topic=error&selected_module=' . $moduleId);
        }

        $this->redirect('/handbook/edit?topic=deleted&selected_module=' . $moduleId);
    }

    private function moduleId(): int
    {
        $value = $_POST['module_id'] ?? null;

        if (!is_string($value) || preg_match('/^[1-9][0-9]*$/', $value) !== 1) {
            $this->redirect('/handbook/edit?topic=error');
        }

        return (int) $value;
    }

    private function topicId(): int
    {
        $value = $_POST['topic_id'] ?? null;

        if (!is_string($value) || preg_match('/^[1-9][0-9]*$/', $value) !== 1) {
            $this->redirect('/handbook/edit?topic=error');
        }

        return (int) $value;
    }

    private function title(int $moduleId, int $topicId): string
    {
        $title = trim((string) ($_POST['title'] ?? ''));

        if ($title === '' || mb_strlen($title) > 255) {
            $this->redirect('/handbook/edit?topic=invalid&selected_module=' . $moduleId . '&selected_topic=' . $topicId);
        }

        return $title;
    }

    private function redirect(string $location): never
    {
        header('Location: ' . $location, true, 303);
        exit;
    }
}
