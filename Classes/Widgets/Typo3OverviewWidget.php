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

use ExtensionBuilder\ExtensionBuilderTypo3\Service\BackendService;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.14
 */

final class Typo3OverviewWidget implements WidgetInterface, RequestAwareWidgetInterface
{
    private ServerRequestInterface $request;

    /**
     * @since 0.14
     */
    public function __construct(
        private readonly WidgetConfigurationInterface $configuration,
        private readonly BackendViewFactory $backendViewFactory,
        private readonly BackendService $backendService,
        /** @var array<string, mixed> */
        private readonly array $options = [],
    ) {}

    /**
     * @since 0.14
     */
    public function renderWidgetContent(): string
    {
        $view = $this->backendViewFactory->create(
            $this->request,
            [
                'typo3/cms-dashboard',
                'extensionbuilder/extensionbuilder-typo3',
            ]
        );

        $developerCount = $this->backendService->countDeveloper();
        $projectCount = count($this->backendService->projects);
        $vendorCount = count($this->backendService->vendorsAndExtensions ?? []);

        $extensionCount = 0;
        foreach ($this->backendService->vendorsAndExtensions as $vendorValue) {
            $extensionCount += count($vendorValue['extensions'] ?? []);
        }


        $view->assignMultiple([
            'configuration' => $this->configuration,
            'options' => $this->options,
            'stats' => [
                'developers' => $developerCount,
                'vendors' => $vendorCount,
                'extensions' => $extensionCount,
                'projects' => $projectCount,
            ],
        ]);

        return $view->render('Typo3OverviewWidget');
    }

    /**
     * @since 0.14
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @since 0.14
     */
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }
}