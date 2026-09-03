<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceKeyword;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceScheme;
use TYPO3\CMS\Core\Type\Map;

/**
 *
 * Migration:
 * - Target: ExtensionBuilder Core 1.x
 * - Status: legacy
 *
 * @extensionbuilderCoreMajorVersion 0
 * @extensionbuilderMigrationStatus legacy
 *
 * @since 0.13
 */

return Map::fromEntries(
    [
        Scope::backend(),
        new MutationCollection(
            new Mutation(
                MutationMode::Set,
                Directive::DefaultSrc,
                SourceKeyword::self,
            ),

            new Mutation(
                MutationMode::Set,
                Directive::ScriptSrc,
                SourceKeyword::self,
                SourceKeyword::nonceProxy,
            ),
            new Mutation(
                MutationMode::Set,
                Directive::ScriptSrcElem,
                SourceKeyword::self,
                SourceKeyword::nonceProxy,
            ),
            new Mutation(
                MutationMode::Set,
                Directive::ScriptSrcAttr,
                SourceKeyword::none,
            ),

            new Mutation(
                MutationMode::Set,
                Directive::StyleSrc,
                SourceKeyword::self,
                SourceKeyword::nonceProxy,
            ),
            new Mutation(
                MutationMode::Set,
                Directive::StyleSrcElem,
                SourceKeyword::self,
                SourceKeyword::nonceProxy,
            ),

            // Übergangslösung wegen vorhandener style="" Attribute
            new Mutation(
                MutationMode::Set,
                Directive::StyleSrcAttr,
                SourceKeyword::unsafeInline,
            ),

            new Mutation(
                MutationMode::Set,
                Directive::ImgSrc,
                SourceKeyword::self,
                SourceScheme::data,
            ),

            new Mutation(
                MutationMode::Set,
                Directive::FontSrc,
                SourceKeyword::self,
                SourceScheme::data,
            ),

            new Mutation(
                MutationMode::Set,
                Directive::ConnectSrc,
                SourceKeyword::self,
            ),

            new Mutation(
                MutationMode::Set,
                Directive::WorkerSrc,
                SourceKeyword::self,
                SourceScheme::blob,
            ),

            new Mutation(
                MutationMode::Set,
                Directive::ObjectSrc,
                SourceKeyword::none,
            ),
            new Mutation(
                MutationMode::Set,
                Directive::BaseUri,
                SourceKeyword::self,
            ),
            new Mutation(
                MutationMode::Set,
                Directive::FrameAncestors,
                SourceKeyword::self,
            ),
        ),
    ],
);