<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CsvImporter\ProductCsvImporter;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-products',
    description: 'Imports products from a CSV file into the database',
)]
class ImportProductsCommand extends Command
{
    private const DEFAULT_BATCH_SIZE = 50;

    public function __construct(private readonly ProductCsvImporter $importer) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the CSV file')
            ->addOption(
                'batch-size',
                'b',
                InputOption::VALUE_OPTIONAL,
                'Number of rows to process per batch',
                self::DEFAULT_BATCH_SIZE
            )
            ->addOption('skip-errors', null, InputOption::VALUE_NONE, 'Continue processing even if errors occur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filePath = $input->getArgument('file');
        $batchSize = (int) $input->getOption('batch-size');
        $skipErrors = $input->getOption('skip-errors');

        try {
            $result = $this->importer->import($filePath, $batchSize, $io);

            if ($result->hasErrors() && !$skipErrors) {
                $io->warning(sprintf(
                    'Import completed with errors. Processed: %d, Skipped: %d',
                    $result->getSuccessCount(),
                    $result->getErrorCount()
                ));

                return Command::FAILURE;
            }

            $io->success(sprintf(
                'Import finished! Total: %d, Imported/Updated: %d, Skipped: %d',
                $result->getTotalCount(),
                $result->getSuccessCount(),
                $result->getErrorCount()
            ));

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error('Import failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
