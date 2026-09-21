<?php

declare(strict_types=1);

namespace ManualMuni\Controllers;

use ManualMuni\Models\Repositories\ModuleRepository;
use ManualMuni\Models\Repositories\SectionRepository;
use ManualMuni\Models\Repositories\TopicRepository;
use ManualMuni\Support\CsrfTokenManager;
use ManualMuni\Support\PublicPath;

final readonly class HandbookController
{
    public function __construct(
        private ModuleRepository $moduleRepository,
        private TopicRepository $topicRepository,
        private SectionRepository $sectionRepository,
        private CsrfTokenManager $csrfTokenManager,
    ) {
    }

    public function index(): never
    {
        $modules = $this->moduleRepository->findAll();
        $assetBaseUrl = PublicPath::for('assets');

        require __DIR__ . '/../Views/Handbook/index.php';
        exit;
    }

    public function modules(): never
    {
        $modules = $this->moduleRepository->findAll();
        $topicsByModule = array_fill_keys(array_map(static fn ($module): int => $module->id, $modules), []);
        $topics = $this->topicRepository->findByModuleIds(array_keys($topicsByModule));
        foreach ($topics as $topic) {
            $topicsByModule[$topic->moduleId][] = $topic;
        }
        
        $assetBaseUrl = PublicPath::for('assets');

        require __DIR__ . '/../Views/Handbook/modules.php';
        exit;
    }

    public function edit(): never
    {
        $pageTitle = 'Gestor de Documentación y Protocolos';
        $operatorName = 'Operador Ventanilla';
        $operatorArea = 'Atención Ciudadana';
        $modules = $this->moduleRepository->findAll();
        $moduleCount = count($modules);
        $topicsByModule = array_fill_keys(array_map(static fn ($module): int => $module->id, $modules), []);

        $topics = $this->topicRepository->findByModuleIds(array_keys($topicsByModule));

        foreach ($topics as $topic) {
            $topicsByModule[$topic->moduleId][] = $topic;
        }
        $sectionsByTopic = array_fill_keys(array_map(static fn ($topic): int => $topic->id, $topics), []);

        foreach ($this->sectionRepository->findByTopicIds(array_keys($sectionsByTopic)) as $section) {
            $sectionsByTopic[$section->topicId][] = $section;
        }
        $csrfToken = $this->csrfTokenManager->token();
        $moduleStatus = $_GET['module'] ?? '';
        $moduleStatus = is_string($moduleStatus) ? $moduleStatus : '';
        $topicStatus = $_GET['topic'] ?? '';
        $topicStatus = is_string($topicStatus) ? $topicStatus : '';
        $selectedModuleId = $_GET['selected_module'] ?? '';
        $selectedModuleId = is_string($selectedModuleId) && preg_match('/^[1-9][0-9]*$/', $selectedModuleId) === 1
            ? $selectedModuleId
            : '';
        $selectedTopicId = $_GET['selected_topic'] ?? '';
        $selectedTopicId = is_string($selectedTopicId) && preg_match('/^[1-9][0-9]*$/', $selectedTopicId) === 1
            ? $selectedTopicId
            : '';
        $sectionStatus = $_GET['section'] ?? '';
        $sectionStatus = is_string($sectionStatus) ? $sectionStatus : '';
        $assetBaseUrl = PublicPath::for('assets');

        require __DIR__ . '/../Views/Handbook/edit.php';
        exit;
    }
}
