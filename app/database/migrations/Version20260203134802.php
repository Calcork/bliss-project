<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20260203134802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create languages and users tables';
    }

    public function up(Schema $schema): void
    {
        $languages = $schema->createTable('languages');
        $languages->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $languages->addColumn('name', Types::STRING, ['length' => 100]);
        $languages->addColumn('locale', Types::STRING, ['length' => 2]);
        $languages->setPrimaryKey(['id']);
        $languages->addUniqueIndex(['locale']);

        $users = $schema->createTable('users');
        $users->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $users->addColumn('name', Types::STRING, ['length' => 255]);
        $users->addColumn('email', Types::STRING, ['length' => 180]);
        $users->addColumn('password_hash', Types::STRING, ['length' => 255]);
        $users->addColumn('email_verification_token', Types::STRING, ['length' => 64, 'notnull' => false]);
        $users->addColumn('email_verified_at', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $users->addColumn('created_at', Types::DATETIME_MUTABLE);
        $users->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $users->addColumn('language_id', Types::INTEGER);
        $users->setPrimaryKey(['id']);
        $users->addUniqueIndex(['email']);
        $users->addUniqueIndex(['email_verification_token']);
        $users->addForeignKeyConstraint('languages', ['language_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('users');
        $schema->dropTable('languages');
    }
}
