<?php

declare(strict_types=1);

namespace App\Tests\Service\CsvImporter;

use App\Service\CsvImporter\CsvReader;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CsvReaderTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'csv_test');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }

    public function testOpenAndReadRows(): void
    {
        file_put_contents($this->tmpFile, "name,price,description\nProduct A,10,Test\nProduct B,20,Demo");

        $reader = new CsvReader(['name', 'price']);
        $headers = $reader->open($this->tmpFile);

        $this->assertEquals(['name', 'price', 'description'], $headers);

        $rows = iterator_to_array($reader->readRows());

        $this->assertCount(2, $rows);
        $this->assertEquals(['name' => 'Product A', 'price' => '10', 'description' => 'Test'], $rows[2]);
        $this->assertEquals(['name' => 'Product B', 'price' => '20', 'description' => 'Demo'], $rows[3]);

        $reader->close();
    }

    public function testMissingRequiredColumnsThrows(): void
    {
        file_put_contents($this->tmpFile, "product,price\nA,10");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing required columns: name');

        $reader = new CsvReader(['name', 'price']);
        $reader->open($this->tmpFile);
    }

    public function testEmptyFileThrows(): void
    {
        file_put_contents($this->tmpFile, "");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CSV file is empty or missing header row.');

        $reader = new CsvReader(['name']);
        $reader->open($this->tmpFile);
    }

    public function testHandlesBomInHeader(): void
    {
        file_put_contents($this->tmpFile, "\xEF\xBB\xBFname,price\nA,10");

        $reader = new CsvReader(['name', 'price']);
        $headers = $reader->open($this->tmpFile);

        $this->assertEquals(['name', 'price'], $headers);
    }

    public function testCloseWithoutOpenDoesNotThrow(): void
    {
        $reader = new CsvReader();
        $reader->close();

        $this->assertTrue(true);
    }
}
