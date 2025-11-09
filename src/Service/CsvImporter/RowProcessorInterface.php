<?php

declare(strict_types= 1);

namespace App\Service\CsvImporter;

interface RowProcessorInterface
{
    public function process(array $row): void;
}