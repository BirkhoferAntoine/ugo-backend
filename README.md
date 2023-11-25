# UGO Back End

## Description
This project is a Symfony API designed to manage a SQLite database. It includes a command for importing data from CSV files and integrates PHPUnit tests.

## Database Configuration
The SQLite database is set to be stored in `var/database/ugo.sqlite`. The default CSV files for import are expected in the `var/data` directory, named `customers.csv` and `purchases.csv`.

## Installation
Follow these steps to set up the application:

1. **Installing Dependencies**:
   ```bash
   composer install
   ```

2. **Creating the Database**:
   ```bash
   php bin/console doctrine:database:create
   ```

3. **Database Migration**:
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

## CSV Data Import
To import data from CSV files:

```bash
php bin/console ugo:orders:import
```

## Tests
To run PHPUnit tests:

```bash
php bin/phpunit
```

## Launching the Application
Use the following command to start the Symfony development server:

```bash
symfony serve
```

---

*This README is intended for the development version of the UGO API. Additional configurations may be required for production deployment.*
```
