<?php

declare(strict_types=1);

namespace App\Service\CsvImporter;

class ValidationResult
{
    private function __construct(
        private readonly bool $valid,
        private readonly ?string $errorMessage = null,
    ) {
    }

    public static function valid(): self
    {
        return new self(true);
    }

    public static function invalid(string $errorMessage): self
    {
        return new self(false, $errorMessage);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getMessage(): ?string
    {
        return $this->errorMessage;
    }
}