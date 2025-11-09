<?php

declare(strict_types=1);

namespace App\Service\CsvImporter;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\CsvImporter\RowProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class ProductRowProcessor implements RowProcessorInterface
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function process(array $row): void
    {
        $product = $this->findOrCreateProduct($row['name']);

        $product->setName($row['name']);
        $product->setPrice((float) $row['price']);

        if (!empty($row['description'])) {
            $product->setDescription($row['description']);
        }

        if (!empty($row['created_at'])) {
            $product->setCreatedAt(new DateTimeImmutable($row['created_at']));
        }

        if (!empty($row['updated_at'])) {
            $product->setUpdatedAt(new DateTimeImmutable($row['updated_at']));
        }

        $this->entityManager->persist($product);
    }

    private function findOrCreateProduct(string $name): Product
    {
        return $this->productRepository->findOneBy(['name' => $name]) ?? new Product();
    }
}
