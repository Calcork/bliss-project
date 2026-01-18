<?php

namespace Hizech\Bliss\DoctrineWrapper;

use Doctrine\ORM\EntityManager;
use Hizech\Bliss\App\Services\DbEntityManager;

/** @phpstan-ignore class.extendsFinalByPhpDoc */
class DoctrineWrapper extends EntityManager implements DbEntityManager
{}