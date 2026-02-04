<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203120116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE languages (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, locale VARCHAR(2) NOT NULL, UNIQUE INDEX UNIQ_A0D153794180C698 (locale), PRIMARY KEY (id))');
        $this->addSql("INSERT INTO languages (name, locale) VALUES ('English', 'en'), ('Hebrew', 'he')");
        $this->addSql('ALTER TABLE users ADD language_id INT NULL');
        $this->addSql("UPDATE users SET language_id = (SELECT id FROM languages WHERE locale = 'en')");
        $this->addSql('ALTER TABLE users MODIFY language_id INT NOT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E982F1BAF4 FOREIGN KEY (language_id) REFERENCES languages (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E982F1BAF4 ON users (language_id)');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e9_email_token TO UNIQ_1483A5E9C4995C67');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE languages');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E982F1BAF4');
        $this->addSql('DROP INDEX IDX_1483A5E982F1BAF4 ON users');
        $this->addSql('ALTER TABLE users DROP language_id');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e9c4995c67 TO UNIQ_1483A5E9_EMAIL_TOKEN');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
