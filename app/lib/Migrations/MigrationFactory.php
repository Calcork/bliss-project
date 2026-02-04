<?php

namespace App\Lib\Migrations;

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Hizech\Bliss\DoctrineWrapper\DoctrineWrapper;

class MigrationFactory
{
    /**
     * @param array<string, mixed> $config
     */
    public static function create(DoctrineWrapper $em, array $config): DependencyFactory
    {

        return DependencyFactory::fromEntityManager(
            new ConfigurationArray($config),
            new ExistingEntityManager($em)
        );

    }
}
