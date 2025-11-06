<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251104132454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blacklist (id INT AUTO_INCREMENT NOT NULL, ip VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ip (id INT AUTO_INCREMENT NOT NULL, ip VARCHAR(15) NOT NULL, type VARCHAR(50) NOT NULL, continent_code VARCHAR(50) DEFAULT NULL, continent_name VARCHAR(50) DEFAULT NULL, country_code VARCHAR(50) DEFAULT NULL, country_name VARCHAR(50) DEFAULT NULL, region_code VARCHAR(50) DEFAULT NULL, region_name VARCHAR(50) DEFAULT NULL, city VARCHAR(50) DEFAULT NULL, zip VARCHAR(50) DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, radius VARCHAR(50) DEFAULT NULL, ip_routing_type VARCHAR(50) DEFAULT NULL, connection_type VARCHAR(50) DEFAULT NULL, geo_name_id INT DEFAULT NULL, capital VARCHAR(50) DEFAULT NULL, language_code VARCHAR(50) DEFAULT NULL, language_name VARCHAR(50) DEFAULT NULL, language_name_native VARCHAR(50) DEFAULT NULL, country_flag VARCHAR(255) DEFAULT NULL, country_flag_emoji VARCHAR(50) DEFAULT NULL, country_flag_emoji_unicode VARCHAR(255) DEFAULT NULL, calling_code VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE blacklist');
        $this->addSql('DROP TABLE ip');
    }
}
