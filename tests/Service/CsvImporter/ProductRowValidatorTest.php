<?php

declare(strict_types=1);

namespace App\Tests\Service\CsvImporter;

use App\Service\CsvImporter\ProductRowValidator;
use App\Service\CsvImporter\ValidationResult;
use PHPUnit\Framework\TestCase;

class ProductRowValidatorTest extends TestCase
{
    private ProductRowValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new ProductRowValidator();
    }

    public function testValidRow(): void
    {
        $row = [
            'name' => 'Product A',
            'price' => '99.99',
            'created_at' => '2025-11-09 12:00:00',
            'updated_at' => '2025-11-09 12:30:00',
        ];

        $result = $this->validator->validate($row, 1);

        $this->assertTrue($result->isValid());
    }

    public function testMissingRequiredField(): void
    {
        $row = [
            'price' => '10.0',
        ];

        $result = $this->validator->validate($row, 2);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString("name", $result->getMessage());
    }

    public function testInvalidPrice(): void
    {
        $row = [
            'name' => 'Product B',
            'price' => 'abc',
        ];

        $result = $this->validator->validate($row, 3);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString("price", $result->getMessage());
    }

    public function testNegativePrice(): void
    {
        $row = [
            'name' => 'Product C',
            'price' => '-5',
        ];

        $result = $this->validator->validate($row, 4);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString("negative", $result->getMessage());
    }

    public function testInvalidCreatedAtDate(): void
    {
        $row = [
            'name' => 'Product D',
            'price' => '20',
            'created_at' => 'invalid-date',
        ];

        $result = $this->validator->validate($row, 5);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString("created_at", $result->getMessage());
    }

    public function testInvalidUpdatedAtDate(): void
    {
        $row = [
            'name' => 'Product E',
            'price' => '50',
            'updated_at' => 'not-a-date',
        ];

        $result = $this->validator->validate($row, 6);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString("updated_at", $result->getMessage());
    }

    public function testEmptyOptionalDates(): void
    {
        $row = [
            'name' => 'Product F',
            'price' => '100',
            'created_at' => '',
            'updated_at' => null,
        ];

        $result = $this->validator->validate($row, 7);

        $this->assertTrue($result->isValid());
    }
}
