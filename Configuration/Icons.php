<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;

/**
 * @extensionbuilderCoreMajorVersion 1
 *
 * @since 0.12
 */

return [
    'eb-logo-svg' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:extensionbuilder_typo3/Resources/Public/Icons/eb-logo.svg',
    ],
    'eb-logo-png' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:extensionbuilder_typo3/Resources/Public/Icons/eb-logo.png',
    ],
];