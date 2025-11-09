<?php

declare(strict_types=1);

namespace App\Service\CsvImporter;

interface CsvReaderInterface
{
    /**
     * @return array<string>
     */
    public function open(string $filePath): array;

    /**
     * @return iterable<int, array<string, string>>
     */
    public function readRows(): iterable;

    public function close(): void;
}
