<?php

declare(strict_types=1);

namespace App\Service\CsvImporter;

use App\Service\CsvImporter\CsvReaderInterface;
use RuntimeException;

class CsvReader implements CsvReaderInterface
{
    private const BOM_PATTERN = '/^\x{FEFF}/u';

    private $handle;
    private array $headers = [];
    private array $requiredColumns;

    public function __construct(array $requiredColumns = [])
    {
        $this->requiredColumns = $requiredColumns;
    }

    public function open(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new RuntimeException("CSV file not found or not readable: $filePath");
        }

        $this->handle = fopen($filePath, 'r');
        if (!$this->handle) {
            throw new RuntimeException("Failed to open CSV file: $filePath");
        }

        $header = fgetcsv($this->handle);
        if (!$header) {
            throw new RuntimeException("CSV file is empty or missing header row.");
        }

        $this->headers = $this->normalizeHeaders($header);
        $this->validateRequiredColumns();

        return $this->headers;
    }

    public function readRows(): iterable
    {
        if (!$this->handle) {
            throw new RuntimeException("CSV file not opened. Call open() first.");
        }

        $rowNumber = 1;

        while (($row = fgetcsv($this->handle)) !== false) {
            $rowNumber++;

            if (count($row) !== count($this->headers)) {
                continue; 
            }

            $data = array_combine($this->headers, $row);
            $data = array_map(fn($val) => $val !== null ? trim($val) : null, $data);

            yield $rowNumber => $data;
        }
    }

    public function close(): void
    {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($col) {
            $col = trim($col);
            $col = preg_replace(self::BOM_PATTERN, '', $col);
            return strtolower($col);
        }, $headers);
    }

    private function validateRequiredColumns(): void
    {
        $missingColumns = array_diff($this->requiredColumns, $this->headers);
        
        if (!empty($missingColumns)) {
            throw new RuntimeException(sprintf(
                "Missing required columns: %s. Available: %s",
                implode(', ', $missingColumns),
                implode(', ', $this->headers)
            ));
        }
    }
}
