<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231120180017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__order AS SELECT id, customer_id_id, product_id, quantity, price, currency, date FROM "order"');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('CREATE TABLE "order" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, customer_id INTEGER NOT NULL, product_id INTEGER DEFAULT NULL, quantity INTEGER DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, currency VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, CONSTRAINT FK_F52993989395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "order" (id, customer_id, product_id, quantity, price, currency, date) SELECT id, customer_id_id, product_id, quantity, price, currency, date FROM __temp__order');
        $this->addSql('DROP TABLE __temp__order');
        $this->addSql('CREATE INDEX IDX_F52993989395C3F3 ON "order" (customer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__order AS SELECT id, customer_id, product_id, quantity, price, currency, date FROM "order"');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('CREATE TABLE "order" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, customer_id_id INTEGER NOT NULL, product_id INTEGER DEFAULT NULL, quantity INTEGER DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, currency VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, CONSTRAINT FK_F5299398B171EB6C FOREIGN KEY (customer_id_id) REFERENCES customer (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "order" (id, customer_id_id, product_id, quantity, price, currency, date) SELECT id, customer_id, product_id, quantity, price, currency, date FROM __temp__order');
        $this->addSql('DROP TABLE __temp__order');
        $this->addSql('CREATE INDEX IDX_F5299398B171EB6C ON "order" (customer_id_id)');
    }
}
