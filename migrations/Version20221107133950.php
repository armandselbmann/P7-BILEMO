<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221107133950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer_user (id INT AUTO_INCREMENT NOT NULL, customers_id INT NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(50) NOT NULL, postal_code VARCHAR(10) NOT NULL, adress VARCHAR(255) NOT NULL, city VARCHAR(50) NOT NULL, country VARCHAR(50) NOT NULL, phone VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_D902723EC3568B40 (customers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer_user ADD CONSTRAINT FK_D902723EC3568B40 FOREIGN KEY (customers_id) REFERENCES customer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_user DROP FOREIGN KEY FK_D902723EC3568B40');
        $this->addSql('DROP TABLE customer_user');
    }
}
