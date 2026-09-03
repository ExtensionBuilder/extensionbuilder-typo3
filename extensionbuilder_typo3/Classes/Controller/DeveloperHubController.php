<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use Psr\Http\Message\ResponseInterface;

use TYPO3\CMS\Core\Utility\GeneralUtility;

use ExtensionBuilder\ExtensionBuilderTypo3\Tools;
use ExtensionBuilder\ExtensionBuilderTypo3\Tools\MarkdownRenderer;

/**
 *
 * Migration:
 * - Target: ExtensionBuilder Core 1.x
 * - Status: legacy
 *
 * @extensionbuilderCoreMajorVersion 0
 * @extensionbuilderMigrationStatus legacy
 *
 * @since 0.14
 */

#[AsController]
final class DeveloperHubController extends ExtensionBuilderController
{

    /**
     * @since 0.14
     */
    final function showAction(): ResponseInterface {
        $bodyParams = array_merge($this->request->getQueryParams() ?? [], $this->request->getParsedBody() ?? []);
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $this->pageRenderer->loadJavaScriptModule('@extensionbuilder/typo3/hotkeys.js');

        $mdFile = self::sanitizeMarkdownPath((string)($bodyParams['mdFile'] ?? 'index.md'));
        $markdownFile = GeneralUtility::getFileAbsFileName(
            'EXT:extensionbuilder_typo3/Documentation/' . $mdFile
        );
        if (!is_file($markdownFile)) {
            $markdown = "# Documentation file '" . $mdFile . "' not found.";
        } else {
            $markdown = (string)file_get_contents($markdownFile);
        }

        $markdown = $this->rewriteMarkdownLinksToBackendLinks($markdown);

        $markdownHtml = $this->renderMarkdown($markdown);

        $this->moduleTemplate->assignMultiple([
            'lllBase' => $this->ebBackendService->lll,
            'markdownHtml' => $markdownHtml,
        ]);

        $this->addDocHeaderModuleDropDown('DeveloperHub');
        $this->addDocHeaderCloseButton('list', 'Extension');

        return $this->moduleTemplate->renderResponse('DeveloperHub');
    }

    /**
     * @since 0.14
     */
    private function renderMarkdown(string $markdown): string
    {
        $renderer = new MarkdownRenderer([
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink'  => true,
            'heading_html_class' => 'documentation-heading-permalink',
        ]);

        return (string)$renderer->convert($markdown);
    }

    /**
     * @since 0.14
     */
    private function rewriteMarkdownLinksToBackendLinks(string $markdown): string
    {
        return preg_replace_callback(
            '/\[([^\]]+)\]\(([^)\s]+\.md)\)/i',
            function (array $matches): string {
                $label = $matches[1];
                $targetMdFile = self::sanitizeMarkdownPath(rawurldecode($matches[2]));
                $backendUrl = $this->buildMarkdownUrl($targetMdFile);
                return '[' . $label . '](' . $backendUrl . ')';
            },
            $markdown
        );
    }

    /**
     * @since 0.14
     */
    private function buildMarkdownUrl(string $targetMdFile): string
    {
        return (string)$this->uriBuilder
            ->uriFor(
                'show', // only action name, not `myAction`
                [ 'mdFile' => $targetMdFile ],
                'DeveloperHub', // only controller name, not `MyController`
            );
    }

    /**
     * @since 0.14
     */
    private function sanitizeMarkdownPath(string $mdFile): string
    {
        $mdFile = trim(rawurldecode($mdFile));
        $mdFile = trim($mdFile, " \t\n\r\0\x0B<>");

        $mdFile = str_replace('\\', '/', $mdFile);
        $mdFile = ltrim($mdFile, '/');

        if (str_contains($mdFile, '..')) {
            return 'index.md';
        }

        if (!str_ends_with(strtolower($mdFile), '.md')) {
            return 'index.md';
        }

        return $mdFile;
    }

}