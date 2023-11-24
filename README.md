# UGO Back End Side

## Symfony API & Sqlite database with CSV command line import and phpUnit test


Sqlite database will be created in var/database/ugo.sqlite
Default csv import directory is located in var/data and is expecting customers.csv and purchases.csv


composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console ugo:orders:import

symfony serve

