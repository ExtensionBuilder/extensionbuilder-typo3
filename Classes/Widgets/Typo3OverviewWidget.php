<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Widgets;

use ExtensionBuilder\ExtensionBuilderTypo3\Tools;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\View\BackendViewFactory;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Dashboard\Widgets\RequestAwareWidgetInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;

final class Typo3OverviewWidget implements WidgetInterface, RequestAwareWidgetInterface
{
    private ServerRequestInterface $request;

    public function __construct(
        private readonly WidgetConfigurationInterface $configuration,
        private readonly BackendViewFactory $backendViewFactory,
        /** @var array<string, mixed> */
        private readonly array $options = [],
    ) {}

    public function renderWidgetContent(): string
    {
        $view = $this->backendViewFactory->create($this->request);

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'options' => $this->options,
            'projectTodos' => [
                'test' => [
                    'name' => 'Testprojekt',
                    'todo' => 'Das ist ein Test-To-do',
                    'scope' => 'Test',
                ],
            ],
            'stats' => [
                'projectsWithTodos' => 1,
                'lastUpdated' => (new \DateTimeImmutable())->format('d.m.Y H:i'),
            ],
        ]);

        return $view->render(
            'EXT:extensionbuilder_typo3/Resources/Private/Templates/Widget/Typo3OverviewWidget'
        );
    }

    public function renderWidgetContent1(): string
    {
        return '<div class="widget-content-main">
            <h3>Extension Builder</h3>
            <p>Dashboard-Widget funktioniert.</p>
        </div>';
    }

    /** @return array<string, mixed> */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function renderWidgetContentAlt(): string
    {
        $projectTodos = $this->getProjectTodos();

        $view = $this->backendViewFactory->create($this->request);

        $view->assignMultiple([
            'configuration' => $this->configuration,
            'options' => $this->options,
            'projectTodos' => $projectTodos,
            'stats' => [
                'projectsWithTodos' => count($projectTodos),
                'lastUpdated' => (new \DateTimeImmutable())->format('d.m.Y H:i'),
            ],
        ]);

        return $view->render('Widget/Typo3OverviewWidget');
    }

    /** @return array<string, mixed> */
    private function getProjectTodos(): array
    {
        $projects = [];

        /*
         * ExtensionBuilder Repository bestimmen
         */
        $repositoryPath = Environment::getProjectPath()
            . DIRECTORY_SEPARATOR
            . (Environment::isComposerMode() ? 'ExtensionBuilder' : '.ExtensionBuilder')
            . DIRECTORY_SEPARATOR;

        /*
         * Globale Projekte
         */
        $globalProjectsFile = $repositoryPath . 'globalProjects.json';

        if (is_file($globalProjectsFile)) {
            $data = Tools\Json::read($globalProjectsFile);

            foreach (($data['projects'] ?? []) as $projectKey => $project) {
                $projects[$projectKey] = $project;
            }
        }

        /*
         * Vendor-Projekte
         */
        if (is_dir($repositoryPath)) {
            foreach (scandir($repositoryPath) ?: [] as $vendorName) {
                if ($vendorName === '.' || $vendorName === '..') {
                    continue;
                }

                $vendorFile = $repositoryPath
                    . $vendorName
                    . DIRECTORY_SEPARATOR
                    . 'vendor.json';

                if (!is_file($vendorFile)) {
                    continue;
                }

                $data = Tools\Json::read($vendorFile);

                foreach (($data['vendor']['projects'] ?? []) as $projectKey => $project) {
                    $projects[$projectKey] = $project;
                }
            }
        }

        /*
         * Projekte des aktuellen Backend-Benutzers
         */
        $username = (string)($GLOBALS['BE_USER']->user['username'] ?? '');

        if ($username !== '') {
            $developerFile = $repositoryPath
                . 'developer.'
                . $username
                . '.json';

            if (is_file($developerFile)) {
                $data = Tools\Json::read($developerFile);

                foreach (($data['developer']['projects'] ?? []) as $projectKey => $project) {
                    $projects[$projectKey] = $project;
                }
            }
        }

        /*
         * Nur Projekte mit To-do zurückgeben
         */
        $projectTodos = [];

        foreach ($projects as $projectKey => $project) {

            if ((bool)($project['ebDisable'] ?? false)) {
                continue;
            }

            $todo = trim((string)($project['ebDevTodo'] ?? ''));

            if ($todo === '') {
                continue;
            }

            $projectTodos[(string)$projectKey] = [
                'name' => (string)(
                    $project['name']
                    ?? $project['projectName']
                    ?? $projectKey
                ),
                'todo' => $todo,
                'scope' => (string)($project['scope'] ?? ''),
            ];
        }

        return $projectTodos;
    }
}
