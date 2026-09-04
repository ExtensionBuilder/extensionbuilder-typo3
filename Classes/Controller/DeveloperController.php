<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Controller;

use ExtensionBuilder\ExtensionBuilderTypo3\Tools;
use Psr\Http\Message\ResponseInterface;

use TYPO3\CMS\Backend\Attribute\AsController;

use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/**
 * Migration:
 * - Target: ExtensionBuilder Core 1.x
 * - Status: legacy
 *
 * @extensionbuilderCoreMajorVersion 0
 * @extensionbuilderMigrationStatus legacy
 *
 * @since 0.12
 */
#[AsController]
final class DeveloperController extends ExtensionBuilderController
{
    /**
     * @since 0.12
     */
    final public function editAction(): ResponseInterface
    {
        $bodyParams = array_merge($this->request->getQueryParams(), is_array($this->request->getParsedBody()) ? $this->request->getParsedBody() : []);

        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');
        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/buildfields.js');

        $this->pageRenderer->addCssFile('EXT:extensionbuilder_typo3/Resources/Public/Css/extensionbuilder.css');

        if ($this->ebBackendService->noDeveloper) {
            $this->moduleTemplate->addFlashMessage(
                '', // ToDo LLL
                'To start development, add your data.', // ToDo LLL
                ContextualFeedbackSeverity::INFO,
                true
            );

            $this->ebBackendService->developer['author'] = $this->getBackendUser()->user['realName'] ?? '';
            $this->ebBackendService->developer['author_email'] = $this->getBackendUser()->user['email'] ?? '';

            // ToDo: array "author_company"

        }

        switch ($bodyParams['cmd'] ?? '') {
            case 'save':
                Tools\ConfigArray::checkFieldsToBool(
                    $this->ebBackendService->developerConfiguration['fieldsEdit'],
                    $bodyParams['developer']
                );

                Tools\ConfigArray::arrayMerge($this->ebBackendService->developer, $bodyParams['developer']);

                $this->ebBackendService->writeDeveloper();

                $this->flashMessage(
                    '',
                    $this->getTranslatedLabel(
                        $this->request,
                        $this->ebBackendService->lll . '.developer.xlf:savingDeveloperSetings',
                    )
                );
                break;
        }

        $projects = [];
        $projects['no'] = $this->getTranslatedLabel(
            $this->request,
            $this->ebBackendService->lll . '.project.xlf:noProject',
        );

        foreach ($this->ebBackendService->projects ?? [] as $projectName => $projectData) {
            $projects[$projectName] = $projectData['name'];
        }

        $vendors = [];
        $vendors['all'] = $this->getTranslatedLabel(
            $this->request,
            $this->ebBackendService->lll . '.vendor.xlf:showAllVendors',
        );
        $vendors['no'] = $this->getTranslatedLabel(
            $this->request,
            $this->ebBackendService->lll . '.vendor.xlf:noVendors',
        );

        foreach ($this->ebBackendService->vendors ?? [] as $vendorName => $vendorData) {
            $vendors[$vendorName] = $vendorData['vendorName'];
        }

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'configuration' => $this->ebBackendService->configuration,
            'developerData' => $this->ebBackendService->developer,
            'developerConfiguration' => $this->ebBackendService->developerConfiguration,
            'selectFields' => ['vendors' => $vendors , 'projects' => $projects],
        ]);

        $this->addDocHeaderModuleDropDown(
            'Developer',
        );
        $this->addDocHeaderCloseButton(
            'list',
            'Extension',
        );
        $this->addDocHeaderSaveButton(
            'developer-form',
            'Develope',
        );

        return $this->moduleTemplate->renderResponse('Developer');
    }

}
