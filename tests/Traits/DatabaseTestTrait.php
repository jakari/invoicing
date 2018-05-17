<?php

namespace Invoicing\Tests\Traits;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait DatabaseTestTrait
{
    abstract protected function getContainer() :ContainerInterface;

    protected function resetDatabase():void
    {
        $em = $this->getEntityManager();
        $purger = new ORMPurger($em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purger->purge();
    }

    protected function getEntityManager():EntityManager
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }
}
