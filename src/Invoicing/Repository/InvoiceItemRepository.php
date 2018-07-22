<?php

namespace Invoicing\Repository;

use Doctrine\ORM\EntityRepository;
use Invoicing\Entity\Invoice\InvoiceItem;

class InvoiceItemRepository extends EntityRepository
{
    public function get($id): InvoiceItem {
        return $this->find($id);
    }

    public function add(InvoiceItem $item) {
        $this->_em->persist($item);
    }

    public function remove(InvoiceItem $item) {
        $this->_em->remove($item);
    }

    public function flush() {
        $this->_em->flush();
    }
}
