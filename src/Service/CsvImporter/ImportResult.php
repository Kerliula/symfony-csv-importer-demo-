<?php

declare(strict_types=1);

namespace App\Service\CsvImporter;

class ImportResult
{
    private int $totalCount = 0;
    private int $successCount = 0;
    private int $errorCount = 0;

    public function incrementTotal(): void
    {
        $this->totalCount++;
    }

    public function incrementSuccess(): void
    {
        $this->successCount++;
    }

    public function incrementErrors(): void
    {
        $this->errorCount++;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function hasErrors(): bool
    {
        return $this->errorCount > 0;
    }
}
