<?php

declare(strict_types=1);

namespace App\Tests\Service\CsvImporter;

use App\Service\CsvImporter\ProductCsvImporter;
use App\Service\CsvImporter\RowProcessorInterface;
use App\Service\CsvImporter\RowValidatorInterface;
use App\Service\CsvImporter\CsvReaderInterface;
use App\Service\CsvImporter\ImportResult;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProductCsvImporterTest extends TestCase
{
    public function testImportSkipsInvalidRows(): void
    {
        $csvReader = $this->createMock(CsvReaderInterface::class);
        $validator = $this->createMock(RowValidatorInterface::class);
        $processor = $this->createMock(RowProcessorInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $io = $this->createMock(SymfonyStyle::class);

        $rows = [
            ['name' => 'Product A', 'price' => '10'],
            ['name' => '', 'price' => '20'], // invalid row
            ['name' => 'Product B', 'price' => '30'],
        ];

        $csvReader->method('open')->willReturn(['name', 'price']);
        $csvReader->method('readRows')->willReturn($rows);
        $csvReader->expects($this->once())->method('close');

        $validator->method('validate')
            ->willReturnCallback(function ($row, $rowNumber) {
                if (empty($row['name'])) {
                    return \App\Service\CsvImporter\ValidationResult::invalid("Row $rowNumber: name missing");
                }
                return \App\Service\CsvImporter\ValidationResult::valid();
            });

        // Processor only called for valid rows
        $processor->expects($this->exactly(2))
            ->method('process')
            ->willReturnCallback(function ($row) {
                $this->assertContains($row['name'], ['Product A', 'Product B']);
            });

        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('flush');
        $entityManager->expects($this->once())->method('commit');
        $entityManager->method('rollback'); // allow rollback to be called if needed
        $entityManager->method('clear');

        $importer = new ProductCsvImporter($csvReader, $validator, $processor, $entityManager);

        $result = $importer->import('dummy.csv', 50, $io);

        $this->assertInstanceOf(ImportResult::class, $result);
        $this->assertEquals(3, $result->getTotalCount());
        $this->assertEquals(2, $result->getSuccessCount());
        $this->assertEquals(1, $result->getErrorCount());
    }
}
