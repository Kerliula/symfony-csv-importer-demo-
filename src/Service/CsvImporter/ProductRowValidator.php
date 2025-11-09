<?php

declare(strict_types=1);

namespace App\Service\CsvImporter;

use App\Service\CsvImporter\RowValidatorInterface;
use DateTimeImmutable;
use Exception;

class ProductRowValidator implements RowValidatorInterface
{
    private const REQUIRED_FIELDS = ['name', 'price'];

    public function validate(array $row, int $rowNumber): ValidationResult
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (empty($row[$field])) {
                return ValidationResult::invalid(
                    sprintf("Row %d: '%s' is required but missing or empty", $rowNumber, $field)
                );
            }
        }

        if (!is_numeric($row['price'])) {
            return ValidationResult::invalid(
                sprintf("Row %d: 'price' must be a valid number, got '%s'", $rowNumber, $row['price'])
            );
        }

        if ((float) $row['price'] < 0) {
            return ValidationResult::invalid(
                sprintf("Row %d: 'price' cannot be negative", $rowNumber)
            );
        }

        foreach (['created_at', 'updated_at'] as $dateField) {
            if (!empty($row[$dateField])) {
                try {
                    new DateTimeImmutable($row[$dateField]);
                } catch (Exception $e) {
                    return ValidationResult::invalid(
                        sprintf("Row %d: '%s' is not a valid date format", $rowNumber, $dateField)
                    );
                }
            }
        }

        return ValidationResult::valid();
    }
}
