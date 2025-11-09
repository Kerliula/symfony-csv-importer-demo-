<?php

declare(strict_types=1);

namespace App\Tests\Service\CsvImporter;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\CsvImporter\ProductRowProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class ProductRowProcessorTest extends TestCase
{
    public function testProcessCreatesNewProduct(): void
    {
        $row = [
            'name' => 'Product A',
            'price' => '9.99',
            'description' => 'Test product',
            'created_at' => '2025-11-01 10:00:00',
            'updated_at' => '2025-11-01 10:00:00',
        ];

        // Mock the repository to return null (product does not exist)
        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'Product A'])
            ->willReturn(null);

        // Mock the EntityManager
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Product $product) use ($row) {
                $this->assertEquals('Product A', $product->getName());
                $this->assertEquals(9.99, $product->getPrice());
                $this->assertEquals('Test product', $product->getDescription());
                $this->assertEquals(new DateTimeImmutable($row['created_at']), $product->getCreatedAt());
                $this->assertEquals(new DateTimeImmutable($row['updated_at']), $product->getUpdatedAt());
                return true;
            }));

        $processor = new ProductRowProcessor($productRepository, $entityManager);
        $processor->process($row);
    }

    public function testProcessUpdatesExistingProduct(): void
    {
        $existingProduct = new Product();
        $existingProduct->setName('Product A');

        $row = [
            'name' => 'Product A',
            'price' => '15.50',
            'description' => 'Updated description',
            'created_at' => '2025-11-01 10:00:00',
            'updated_at' => '2025-11-02 12:00:00',
        ];

        $productRepository = $this->createMock(ProductRepository::class);
        $productRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'Product A'])
            ->willReturn($existingProduct);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Product $product) use ($row) {
                $this->assertEquals('Product A', $product->getName());
                $this->assertEquals(15.50, $product->getPrice());
                $this->assertEquals('Updated description', $product->getDescription());
                $this->assertEquals(new DateTimeImmutable($row['created_at']), $product->getCreatedAt());
                $this->assertEquals(new DateTimeImmutable($row['updated_at']), $product->getUpdatedAt());
                return true;
            }));

        $processor = new ProductRowProcessor($productRepository, $entityManager);
        $processor->process($row);
    }
}
