<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221107094756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(255) NOT NULL, release_date DATETIME NOT NULL, series VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, maker VARCHAR(50) NOT NULL, price INT NOT NULL, color VARCHAR(50) NOT NULL, platform VARCHAR(50) NOT NULL, network VARCHAR(50) DEFAULT NULL, connector VARCHAR(50) DEFAULT NULL, battery VARCHAR(50) DEFAULT NULL, ram VARCHAR(50) DEFAULT NULL, rom VARCHAR(50) DEFAULT NULL, brand_cpu VARCHAR(50) DEFAULT NULL, speed_cpu VARCHAR(50) DEFAULT NULL, cores_cpu INT DEFAULT NULL, main_cam VARCHAR(50) DEFAULT NULL, sub_cam VARCHAR(50) DEFAULT NULL, display_type VARCHAR(50) DEFAULT NULL, display_size VARCHAR(50) DEFAULT NULL, double_sim TINYINT(1) DEFAULT NULL, card_reader TINYINT(1) DEFAULT NULL, foldable TINYINT(1) DEFAULT NULL, e_sim TINYINT(1) DEFAULT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, depth INT DEFAULT NULL, weight INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE product');
    }
}
