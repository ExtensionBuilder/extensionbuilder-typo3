<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Widgets;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\View\BackendViewFactory;

use TYPO3\CMS\Dashboard\Widgets\RequestAwareWidgetInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;

use ExtensionBuilder\ExtensionBuilderTypo3\Service\BackendService;

final class Typo3OverviewWidget implements WidgetInterface, RequestAwareWidgetInterface
{
    private ServerRequestInterface $request;

    public function __construct(
        private readonly WidgetConfigurationInterface $configuration,
        private readonly BackendViewFactory $backendViewFactory,
        private readonly BackendService $backendService,
        private readonly array $options = [],
    ) {
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function renderWidgetContent(): string
    {
$projectTodos = $this->getProjectTodos();

        $view = $this->backendViewFactory->create($this->request);

//$view->setTemplateRootPaths([
//    GeneralUtility::getFileAbsFileName(
//        'EXT:extensionbuilder_typo3/Resources/Private/Templates/'
//    ),
//]);

$view->assignMultiple([
    'configuration' => $this->configuration,
    'options' => $this->options,
    'projectTodos' => $projectTodos,
    'stats' => [
        'projectsWithTodos' => count($projectTodos),
        'lastUpdated' => (new \DateTimeImmutable())->format('d.m.Y H:i'),
    ],
]);

//        $view->assignMultiple([
//            'configuration' => $this->configuration,
//            'options' => $this->options,
//            'stats' => $this->getStats(),
//        ]);

//    	return $this->moduleTemplate->renderResponse('Developer');

        return $view->render('EXT:extensionbuilder_typo3/Resources/Private/Templates/Widget/Typo3OverviewWidget');

//        return $view->render($this->options['template'] ?? 'Widget/Typo3OverviewWidgetX');
    }

/**
 * Returns active projects that contain developer To-dos.
 *
 * @return array<string, array{name: string, todo: string, scope: string}>
 */
private function getProjectTodos(): array
{
    $projectTodos = [];

    foreach ($this->backendService->projects as $projectKey => $project) {
        if ((bool)($project['ebDisable'] ?? false)) {
            continue;
        }

        $todo = trim((string)($project['ebDevTodo'] ?? ''));
        if ($todo === '') {
            continue;
        }

        $projectTodos[(string)$projectKey] = [
            'name' => (string)($project['name'] ?? $projectKey),
            'todo' => $todo,
            'scope' => (string)($project['scope'] ?? ''),
        ];
    }

    return $projectTodos;
}

    private function getStats(): array
    {
        // Hier später Repository/QueryBuilder einbauen.
        return [
            'contactsTotal' => 128,
            'companiesTotal' => 34,
            'objectsTotal' => 12,
            'lastUpdated' => (new \DateTimeImmutable())->format('d.m.Y H:i'),
        ];
    }

}