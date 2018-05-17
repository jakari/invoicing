<?php

namespace Invoicing\Repository;

use Doctrine\ORM\EntityRepository;

class InvoiceRepository extends EntityRepository
{
    public function transactional(callable $callback) {
        return $this->_em->transactional($callback);
    }
}
