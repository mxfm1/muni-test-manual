<?php

declare(strict_types=1);

namespace ManualMuni\Controllers;

use ManualMuni\Models\Repositories\ModuleRepository;
use ManualMuni\Models\Repositories\SectionRepository;
use ManualMuni\Models\Repositories\TopicRepository;
use ManualMuni\Support\PublicPath;

final readonly class TopicViewController
{
    public function __construct(
        private ModuleRepository $moduleRepository,
        private TopicRepository $topicRepository,
        private SectionRepository $sectionRepository,
    ) {
    }

    public function module(int $moduleId): never
    {
        $module = $this->moduleRepository->findById($moduleId);
        if ($module === null) {
            $this->notFound();
        }

        $topics = $this->topicRepository->findByModuleIds([$module->id]);

        if ($topics !== []) {
            $this->redirect('/handbook/' . $module->id . '/topics/' . $topics[0]->id);
        }

        $topic = null;
        $sections = [];
        $nextTopic = null;
        $assetBaseUrl = PublicPath::for('assets');

        require __DIR__ . '/../Views/Handbook/topic-view.php';
        exit;
    }

    public function topic(int $moduleId, int $topicId): never
    {
        $module = $this->moduleRepository->findById($moduleId);
        if ($module === null) {
            $this->notFound();
        }

        $topic = $this->topicRepository->findById($topicId);
        if ($topic === null || $topic->moduleId !== $module->id) {
            $this->notFound();
        }

        $topics = $this->topicRepository->findByModuleIds([$module->id]);
        $sections = $this->sectionRepository->findByTopicIds([$topic->id]);

        $nextTopic = null;
        foreach ($topics as $index => $candidate) {
            if ($candidate->id === $topic->id) {
                $nextTopic = $topics[$index + 1] ?? null;
                break;
            }
        }

        $assetBaseUrl = PublicPath::for('assets');

        require __DIR__ . '/../Views/Handbook/topic-view.php';
        exit;
    }

    private function redirect(string $location): never
    {
        header('Location: ' . $location, true, 303);
        exit;
    }

    private function notFound(): never
    {
        http_response_code(404);
        echo 'Módulo o tópico no encontrado.';
        exit;
    }
}