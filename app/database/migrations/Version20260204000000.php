<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20260204000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add password reset token and expiry to users table';
    }

    public function up(Schema $schema): void
    {
        $users = $schema->getTable('users');
        $users->addColumn('password_reset_token', Types::STRING, ['length' => 64, 'notnull' => false]);
        $users->addColumn('password_reset_expires_at', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $users->addUniqueIndex(['password_reset_token']);
    }

    public function down(Schema $schema): void
    {
        $users = $schema->getTable('users');
        $users->dropColumn('password_reset_token');
        $users->dropColumn('password_reset_expires_at');
    }
}
