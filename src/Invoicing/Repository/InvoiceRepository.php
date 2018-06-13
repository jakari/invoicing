<?php

namespace Invoicing\Repository;

use Doctrine\ORM\EntityRepository;
use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Exception\NoReferenceException;

class InvoiceRepository extends EntityRepository
{
    public function transactional(callable $callback) {
        return $this->_em->transactional($callback);
    }

    public function create(Invoice $invoice)
    {
        $this->_em->persist($invoice);
    }

    public function update(Invoice $invoice)
    {
        $this->_em->flush($invoice);
    }

    /**
     * @throws NoReferenceException
     */
    public function getNextReference() : int
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('MAX(i.referenceNumber)')
            ->from(Invoice::class, 'i');

        $result = $qb->getQuery()->getSingleScalarResult();

        if (!$result) {
            throw new NoReferenceException();
        }

        return $result;
    }
}
