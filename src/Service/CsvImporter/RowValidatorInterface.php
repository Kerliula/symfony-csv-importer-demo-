<?php

declare(strict_types=1);

namespace App\Service\CsvImporter;

interface RowValidatorInterface
{
    public function validate(array $row, int $rowNumber): ValidationResult;
}