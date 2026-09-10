<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Controller;

use ExtensionBuilder\ExtensionBuilderTypo3\Service\BackendService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use TYPO3\CMS\Backend\Attribute\AsController;

use TYPO3\CMS\Core\Http\JsonResponse;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */
#[AsController]
final class ExtensionBuilderAjaxController
{
    /**
     * @since 0.12
     */
    public function __construct(
        private readonly BackendService $ebBackendService,
    ) {}

    /**
     * @since 0.12
     */
    public function getFieldsValuesAction(ServerRequestInterface $request): JsonResponse
    {
        $bodyParams = array_merge(
            $request->getQueryParams(),
            is_array($request->getParsedBody()) ? $request->getParsedBody() : []
        );

        $name = (string)($bodyParams['name'] ?? '');
        $config = (string)($bodyParams['config'] ?? '');
        $key = (string)($bodyParams['key'] ?? '');
        $base = (string)($bodyParams['base'] ?? '');
        $fields = (string)($bodyParams['fields'] ?? '');

        $value = [];

        switch ($config) {
            case 'vendors':
                if (!$this->ebBackendService->canAccessVendor($key)) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Access denied.',
                    ], 403);
                }

                $value = $this->ebBackendService->vendors[$key];
                break;
            default:
        }

        $response = new JsonResponse([
            'success' => true,
            'message' => 'Backend 1 AJAX läuft',
            'name' => $name,
            'timestamp' => time(),
            'value' => $value,
        ]);

        return $response;
    }

    /**
     * @since 0.12
     */
    public function checkNameAction(ServerRequestInterface $request): JsonResponse
    {
        $bodyParams = array_merge(
            $request->getQueryParams(),
            is_array($request->getParsedBody()) ? $request->getParsedBody() : []
        );

        $name = trim((string)($bodyParams['name'] ?? ''));

        return new JsonResponse([
            'success' => true,
            'valid' => preg_match('/^[a-zA-Z0-9_]+$/', $name) === 1,
            'name' => $name,
        ]);
    }

    /**
     * @since 0.13
     */
    public function readDevCodeAction(ServerRequestInterface $request): JsonResponse
    {
        $bodyParams = array_merge(
            $request->getQueryParams(),
            is_array($request->getParsedBody()) ? $request->getParsedBody() : []
        );

        $vendorName = trim((string)($bodyParams['vendorName'] ?? ''));
        $extensionName = trim((string)($bodyParams['extensionName'] ?? ''));

        if ($vendorName === '' || $extensionName === '') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Missing vendor or extension name.',
            ], 400);
        }

        try {
            $this->ebBackendService->assertCanAccessVendor($vendorName);
        } catch (\RuntimeException $exception) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        if (!isset($this->ebBackendService->vendorsAndExtensions[$vendorName])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Unknown vendor.',
            ], 404);
        }

        if (!isset($this->ebBackendService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Unknown extension.',
            ], 404);
        }

        try {
            $relativeFileName = $this->sanitizeRelativeFilePath(
                (string)($bodyParams['fileName'] ?? '')
            );
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid file path.',
            ], 400);
        }

        if (!$this->isAllowedCodeFile($relativeFileName)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'File type is not allowed.',
            ], 400);
        }

        $extensionKey = (string)(
            $this->ebBackendService
                ->vendorsAndExtensions[$vendorName]['extensions'][$extensionName]['extension']['extensionName']
            ?? ''
        );

        if ($extensionKey === '') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid extension configuration.',
            ], 500);
        }

        $generatedFile = $this->buildSafePath(
            $this->ebBackendService->extensionPath . $extensionKey . DIRECTORY_SEPARATOR,
            $relativeFileName
        );

        $developerCodeFile = $this->buildSafePath(
            ''
            . $this->ebBackendService->repositoryPath
            . $vendorName . DIRECTORY_SEPARATOR
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR
            . 'DeveloperCode' . DIRECTORY_SEPARATOR,
            $relativeFileName
        );

        $codeContent = '';
        $message = 'File not found.';

        if ($developerCodeFile !== null && is_file($developerCodeFile)) {
            $codeContent = (string)file_get_contents($developerCodeFile);
            $message = 'DeveloperCode file loaded.';
        } elseif ($generatedFile !== null && is_file($generatedFile)) {
            $codeContent = (string)file_get_contents($generatedFile);
            $message = 'Generated file loaded.';
        }

        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'code' => $codeContent,
        ]);
    }

    /**
     * @since 0.13
     */
    public function writeDevCodeAction(ServerRequestInterface $request): JsonResponse
    {
        if (strtoupper($request->getMethod()) !== 'POST') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Method not allowed.',
            ], 405);
        }

        $bodyParams = array_merge(
            $request->getQueryParams(),
            is_array($request->getParsedBody()) ? $request->getParsedBody() : []
        );

        $vendorName = trim((string)($bodyParams['vendorName'] ?? ''));
        $extensionName = trim((string)($bodyParams['extensionName'] ?? ''));

        if ($vendorName === '' || $extensionName === '') {
            return new JsonResponse([
                'success' => false,
                'message' => 'Missing vendor or extension name.',
            ], 400);
        }

        try {
            $this->ebBackendService->assertCanAccessVendor($vendorName);
        } catch (\RuntimeException $exception) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        if (!isset($this->ebBackendService->vendorsAndExtensions[$vendorName])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Unknown vendor.',
            ], 404);
        }

        if (!isset($this->ebBackendService->vendorsAndExtensions[$vendorName]['extensions'][$extensionName])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Unknown extension.',
            ], 404);
        }

        try {
            $relativeFileName = $this->sanitizeRelativeFilePath(
                (string)($bodyParams['fileName'] ?? '')
            );
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid file path.',
            ], 400);
        }

        if (!$this->isAllowedCodeFile($relativeFileName)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'File type is not allowed.',
            ], 400);
        }

        $codeContent = (string)($bodyParams['content'] ?? '');

        $developerCodeFile = $this->buildSafePath( ''
            . $this->ebBackendService->repositoryPath
            . $vendorName . DIRECTORY_SEPARATOR
            . 'TYPO3' . DIRECTORY_SEPARATOR
            . $extensionName . DIRECTORY_SEPARATOR
            . 'DeveloperCode' . DIRECTORY_SEPARATOR,
            $relativeFileName
        );

        if ($developerCodeFile === null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid target path.',
            ], 400);
        }

        $targetDirectory = dirname($developerCodeFile);

        if (!is_dir($targetDirectory)) {
            if (!mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Could not create target directory.',
                ], 500);
            }
        }

        $result = file_put_contents($developerCodeFile, $codeContent, LOCK_EX);

        if ($result === false) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Could not write file.',
            ], 500);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'DeveloperCode file saved.',
        ]);
    }

    /**
     * @since 0.14
     */
    public function buildAction(ServerRequestInterface $request): ResponseInterface
    {
        // Prevent accidental debug output (echo, var_dump, debug HTML, etc.)
        // from breaking the JSON response expected by AjaxRequest.resolve().
        $initialOutputBufferLevel = ob_get_level();
        ob_start();

// 8888
//        $flushT3andPhpCache = $this->ebBackendService->developer['typo3']['flushT3andPhpCache'] ?? false;
//        $analyzeDatabaseStructure = $this->ebBackendService->developer['typo3']['analyzeDatabaseStructure'] ?? false;
//        $rebuildPhpAutoload = $this->ebBackendService->developer['typo3']['rebuildPhpAutoload'] ?? false;
// Error code from Core

        try {
            $bodyParams = array_merge(
                $request->getQueryParams(),
                is_array($request->getParsedBody()) ? $request->getParsedBody() : []
            );

            $vendorName = trim((string)($bodyParams['vendorName'] ?? ''));
            $extensionName = trim((string)($bodyParams['extensionName'] ?? ''));

            if ($vendorName === '') {
                return $this->jsonResponseAndDiscardOutput([
                    'success' => false,
                    'status' => 'error',
                    'message' => 'Missing vendorName.',
                ], 400, $initialOutputBufferLevel);
            }

            if ($extensionName === '') {
                return $this->jsonResponseAndDiscardOutput([
                    'success' => false,
                    'status' => 'error',
                    'message' => 'Missing extensionName.',
                ], 400, $initialOutputBufferLevel);
            }

            $this->ebBackendService->assertCanAccessVendor($vendorName);
            $this->ebBackendService->readExtension($vendorName, $extensionName);

            $this->ebBackendService->build(
                '',
                $vendorName,
                $extensionName,
            );

            $lastBuild = date('d-m-Y H:i:s');

            $this->ebBackendService->vendorsAndExtensions
                [$vendorName]['extensions'][$extensionName]['extensionBuild']['lastBuild'] = $lastBuild;

            $this->ebBackendService->writeExtension($vendorName, $extensionName);

            return $this->jsonResponseAndDiscardOutput([
                'success' => $this->ebBackendService->buildResult === 'OK',
                'status' => $this->ebBackendService->buildResult,
                'message' => $this->ebBackendService->buildResult === 'OK'
                    ? 'Build completed successfully.'
                    : 'Build finished with status: ' . $this->ebBackendService->buildResult,
                'lastBuild' => $lastBuild,
                'debug' => $this->ebBackendService->buildDebug ?? null,
            ], 200, $initialOutputBufferLevel);
        } catch (\Throwable $exception) {
            return $this->jsonResponseAndDiscardOutput([
                'success' => false,
                'status' => 'error',
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'debug' => $this->ebBackendService->buildDebug ?? null,
            ], 500, $initialOutputBufferLevel);
        }
    }

    /**
     * @since 0.14
     */
    private function sanitizeRelativeFilePath(string $fileName): string
    {
        $fileName = str_replace('\\', '/', trim($fileName));

        if (
            $fileName === ''
            || str_contains($fileName, "\0")
            || str_starts_with($fileName, '/')
            || preg_match('#(^|/)\.\.(/|$)#', $fileName)
        ) {
            throw new \InvalidArgumentException('Invalid file path');
        }

        return $fileName;
    }

    /**
     * @since 0.14
     */
    private function isAllowedCodeFile(string $relativeFileName): bool
    {
        $allowedExtensions = [
            'php',
            'html',
            'xlf',
            'yaml',
            'yml',
            'typoscript',
            'tsconfig',
            'js',
            'css',
            'json',
            'md',
            'rst',
        ];

        $extension = strtolower((string)pathinfo($relativeFileName, PATHINFO_EXTENSION));

        if ($extension === '') {
            return false;
        }

        return in_array($extension, $allowedExtensions, true);
    }

    /**
     * @since 0.14
     */
    private function buildSafePath(string $basePath, string $relativeFileName): ?string
    {
        $basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!is_dir($basePath)) {
            return $basePath . str_replace('/', DIRECTORY_SEPARATOR, $relativeFileName);
        }

        $baseRealPath = realpath($basePath);

        if ($baseRealPath === false) {
            return null;
        }

        $targetPath = $basePath . str_replace('/', DIRECTORY_SEPARATOR, $relativeFileName);
        $targetDirectory = dirname($targetPath);

        if (!is_dir($targetDirectory)) {
            $targetDirectoryRealPath = realpath($basePath);
        } else {
            $targetDirectoryRealPath = realpath($targetDirectory);
        }

        if ($targetDirectoryRealPath === false) {
            return null;
        }

        $baseRealPath = rtrim($baseRealPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $targetDirectoryRealPath = rtrim($targetDirectoryRealPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!str_starts_with($targetDirectoryRealPath, $baseRealPath)) {
            return null;
        }

        return $targetPath;
    }

    /**
     * @since 0.14
     *
     * @param array<string, mixed> $payload
     */
    private function jsonResponseAndDiscardOutput(
        array $payload,
        int $statusCode,
        int $initialOutputBufferLevel
    ): JsonResponse {
        $discardedOutput = '';

        while (ob_get_level() > $initialOutputBufferLevel) {
            $bufferContent = ob_get_clean();

            if (is_string($bufferContent) && $bufferContent !== '') {
                $discardedOutput = $bufferContent . $discardedOutput;
            }
        }

        if ($discardedOutput !== '') {
            error_log('ExtensionBuilder buildAction discarded output before JSON response: ' . $discardedOutput);
        }

        return new JsonResponse($payload, $statusCode);
    }
}