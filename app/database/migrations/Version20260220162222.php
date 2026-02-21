<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220162222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE general_settings (id INT NOT NULL, site_name VARCHAR(255) NOT NULL, contact_email VARCHAR(255) NOT NULL, autosystem_email VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE languages (id INT AUTO_INCREMENT NOT NULL, name_t VARCHAR(100) NOT NULL, locale VARCHAR(2) NOT NULL, UNIQUE INDEX UNIQ_A0D153794180C698 (locale), PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, is_admin TINYINT NOT NULL, email VARCHAR(180) NOT NULL, password_hash VARCHAR(255) NOT NULL, email_verification_token VARCHAR(64) DEFAULT NULL, email_verified_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, password_reset_token VARCHAR(64) DEFAULT NULL, password_reset_expires_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, language_id INT NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), UNIQUE INDEX UNIQ_1483A5E9C4995C67 (email_verification_token), UNIQUE INDEX UNIQ_1483A5E96B7BA4B6 (password_reset_token), INDEX IDX_1483A5E982F1BAF4 (language_id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E982F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E982F1BAF4');
        $this->addSql('DROP TABLE general_settings');
        $this->addSql('DROP TABLE languages');
        $this->addSql('DROP TABLE users');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
