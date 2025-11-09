<?php

declare(strict_types=1);

namespace App\Service\CsvImporter;

use App\Service\CsvImporter\CsvReaderInterface;
use App\Service\CsvImporter\RowProcessorInterface;
use App\Service\CsvImporter\RowValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Exception;

class ProductCsvImporter
{
    public function __construct(
        private readonly CsvReaderInterface $csvReader,
        private readonly RowValidatorInterface $validator,
        private readonly RowProcessorInterface $processor,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function import(string $filePath, int $batchSize, ?SymfonyStyle $io = null): ImportResult
    {
        $result = new ImportResult();

        try {
            $this->entityManager->beginTransaction();

            $this->csvReader->open($filePath);

            foreach ($this->csvReader->readRows() as $rowNumber => $row) {
                $result->incrementTotal();

                $validationResult = $this->validator->validate($row, $rowNumber);

                if (!$validationResult->isValid()) {
                    $io?->warning($validationResult->getMessage());
                    $result->incrementErrors();
                    continue;
                }

                try {
                    $this->processor->process($row);
                    $result->incrementSuccess();

                    if ($result->getTotalCount() % $batchSize === 0) {
                        $this->entityManager->flush();
                        $this->entityManager->clear();
                        $io?->text(sprintf('Processed %d rows...', $result->getTotalCount()));
                    }
                } catch (Exception $e) {
                    $io?->warning(sprintf('Error processing row %d: %s', $rowNumber, $e->getMessage()));
                    $result->incrementErrors();
                }
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        } finally {
            $this->csvReader->close();
        }

        return $result;
    }
}
