<?php

declare(strict_types=1);

namespace ExtensionBuilder\ExtensionBuilderTypo3\Command;

// Activate only when V13 support is discontinued.
//use TYPO3\CMS\Core\Attribute\AsNonSchedulableCommand;
//use Symfony\Component\Console\Attribute\AsCommand;
use ExtensionBuilder\ExtensionBuilderTypo3\Service\BuildService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Style\SymfonyStyle;

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

// Activate only when V13 support is discontinued.
//#[AsCommand(
//    name: 'extensionbuilder:typo3',
//    description: 'Extension Builder - Build TYPO3 Extension',
//)]
//#[AsNonSchedulableCommand]
class ExtensionBuilderTypo3 extends Command
{
    /**
     * @since 0.14
     */
    public function __construct(
        private readonly BuildService $buildService,
    ) {
        parent::__construct();
    }

    /**
     * @since 0.14
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Extension Builder - Build TYPO3 Extension')
            ->setHelp('This command requires the following options.')
            ->addOption(
                'developer',
                null,
                InputOption::VALUE_OPTIONAL,
                'Developer name',
                '',
            )
            ->addOption(
                'vendor',
                null,
                InputOption::VALUE_OPTIONAL,
                'Name of the extension vendor',
                '',
            )
            ->addOption(
                'extension',
                null,
                InputOption::VALUE_OPTIONAL,
                'Name of the extension to be created',
                '',
            );
    }

    /**
     * @since 0.14
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $developer = trim((string)($input->getOption('developer') ?? ''));
        $vendor = trim((string)($input->getOption('vendor') ?? ''));
        $extension = trim((string)($input->getOption('extension') ?? ''));

        $ebbs = &$this->buildService->vendorsAndExtensions;

        $this->buildService->buildResult = 'No program generation performed';

        if (!($developer)) {
            $developers = [];
            foreach ($this->buildService->developers as $developerKey => $developerValue) {
                $developers[] = $developerKey;
            }

            $developer = (string)$io->choice(
                'Select developer',
                $developers,
                'import'
            );
        }

        if (!($ebbs[$vendor] ?? false)) {
            $vendors = [];
            foreach ($ebbs as $vendorKey => $vendorValue) {
                $vendors[] = $vendorKey;
            }

            $vendor = (string)$io->choice(
                'Select vendor',
                $vendors,
                'import'
            );
        }

        if (!($ebbs[$vendor]['extensions'][$extension] ?? false)) {
            $extensions = [];
            foreach ($ebbs[$vendor]['extensions'] ?? [] as $extensionKey => $extensionValue) {
                $extensions[] = $extensionKey;
            }

            $extension = (string)$io->choice(
                'Select extension',
                $extensions,
                'import'
            );
        }

        $this->buildService->build($developer, $vendor, $extension);

        $buildInfo
            = "Extension Builder for TYPO3\n\n"
            . 'Developer ' . $developer . "\n"
            . 'Vendor    ' . $vendor . "\n"
            . 'Extension ' . $extension . "\n\n"
            . 'Build result: ' . $this->buildService->buildResult . "\n";

        $io->info($buildInfo);

        if ($this->buildService->buildResult === 'OK') {
            return Command::SUCCESS;
        }
        return Command::FAILURE;

    }

}
