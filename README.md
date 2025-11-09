# Symfony CSV Import Demo

A demo CSV import project built with Symfony to practice data validation, CSV handling, and unit testing.

## Features
- Validate CSV rows for required fields and correct data types
- Handle optional date fields
- Unit tests with PHPUnit

## Requirements
- PHP 8.4.14
- Symfony 7.3.6
- Composer 2.8.12

## Installation

**1.** Clone the repository:
   ```bash
   git clone https://github.com/Kerliula/symfony-csv-import-demo-.git
   cd symfony-csv-import-demo-
   ```

**2.** Install dependencies:
  ```bash 
  composer install
  ```
**3.** Copy .env.example to .env and configure your database and secrets:
  ```bash
  cp .env.example .env
  ```

**4.** Create the database:
  ```bash
  php bin/console doctrine:database:create
  php bin/console doctrine:migrations:migrate
  ```


## Usage

**1.** Prepare a CSV file with the following columns:
   - `name` (required)
   - `price` (required, numeric)
   - `description` (optional)
   - `created_at` (optional, date)
   - `updated_at` (optional, date)

**2.** Place your CSV file in the project (e.g., `data/products.csv`).

**3.** Run the import command with your CSV file:
  ```bash
  php bin/console app:import-products "C:\path\to\your\products.csv"
  ```

## License
This project is for learning purposes and is open for personal use.
