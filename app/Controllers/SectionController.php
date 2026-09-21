<?php

declare(strict_types=1);

namespace ManualMuni\Controllers;

use ManualMuni\Models\Repositories\SectionRepository;
use ManualMuni\Models\Repositories\TopicRepository;
use ManualMuni\Models\Repositories\MediaRepository;
use ManualMuni\Services\ImageStorage;
use ManualMuni\Support\CsrfTokenManager;

final readonly class SectionController
{
    public function __construct(
        private SectionRepository $sectionRepository,
        private TopicRepository $topicRepository,
        private MediaRepository $mediaRepository,
        private ImageStorage $storage,
        private CsrfTokenManager $csrfTokenManager,
    ) {
    }

    public function create(): never
    {
        if (!$this->csrfTokenManager->isValid($_POST['csrf_token'] ?? null)) {
            $this->redirect('/handbook/edit?section=error');
        }

        $topicId = $this->positiveId('topic_id');

        try {
            if (!$this->topicRepository->exists($topicId)) {
                $this->redirect('/handbook/edit?section=error');
            }

            $this->sectionRepository->create($topicId, $this->title(), $this->content());
        } catch (\Throwable) {
            $this->redirect('/handbook/edit?section=error&selected_topic=' . $topicId);
        }

        $this->redirect('/handbook/edit?section=created&selected_topic=' . $topicId);
    }

    public function update(): never
    {
        if (!$this->csrfTokenManager->isValid($_POST['csrf_token'] ?? null)) {
            $this->redirect('/handbook/edit?section=error');
        }

        $sectionId = $this->positiveId('section_id');
        $topicId = $this->sectionRepository->findTopicId($sectionId);
        if ($topicId === null) {
            $this->redirect('/handbook/edit?section=error');
        }

        try {
            if (!$this->sectionRepository->update($sectionId, $this->title(), $this->content())) {
                $this->redirect('/handbook/edit?section=error&selected_topic=' . $topicId);
            }
        } catch (\Throwable) {
            $this->redirect('/handbook/edit?section=error&selected_topic=' . $topicId);
        }

        $this->redirect('/handbook/edit?section=updated&selected_topic=' . $topicId);
    }

    public function delete(): never
    {
        if (!$this->csrfTokenManager->isValid($_POST['csrf_token'] ?? null)) {
            $this->redirect('/handbook/edit?section=error');
        }

        $sectionId = $this->positiveId('section_id');
        $topicId = $this->sectionRepository->findTopicId($sectionId);
        if ($topicId === null) {
            $this->redirect('/handbook/edit?section=error');
        }

        try {
            $media = $this->mediaRepository->findBySectionId($sectionId);

            if (!$this->sectionRepository->delete($sectionId)) {
                $this->redirect('/handbook/edit?section=error&selected_topic=' . $topicId);
            }

            foreach ($media as $item) {
                try {
                    $this->storage->delete($item);
                } catch (\Throwable) {
                    // The database cascade has already removed the media reference.
                }
            }
        } catch (\Throwable) {
            $this->redirect('/handbook/edit?section=error&selected_topic=' . $topicId);
        }

        $this->redirect('/handbook/edit?section=deleted&selected_topic=' . $topicId);
    }

    private function positiveId(string $key): int
    {
        $value = $_POST[$key] ?? null;

        if (!is_string($value) || preg_match('/^[1-9][0-9]*$/', $value) !== 1) {
            $this->redirect('/handbook/edit?section=error');
        }

        return (int) $value;
    }

    private function title(): ?string
    {
        $title = trim((string) ($_POST['title'] ?? ''));

        if (mb_strlen($title) > 255) {
            throw new \InvalidArgumentException('El título de la sección no puede superar 255 caracteres.');
        }

        return $title === '' ? null : $title;
    }

    private function content(): string
    {
        $content = (string) ($_POST['content'] ?? '');

        try {
            $document = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \InvalidArgumentException('El contenido de la sección no es JSON válido.', 0, $exception);
        }

        if (!is_array($document) || ($document['type'] ?? null) !== 'doc') {
            throw new \InvalidArgumentException('El contenido de la sección debe ser un documento Tiptap válido.');
        }

        return json_encode($document, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function redirect(string $location): never
    {
        header('Location: ' . $location, true, 303);
        exit;
    }
}
