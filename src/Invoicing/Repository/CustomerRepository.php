<?php

namespace Invoicing\Repository;

use Doctrine\ORM\EntityRepository;
use Invoicing\Entity\Customer\Customer;

class CustomerRepository extends EntityRepository
{
    public function save(Customer $customer)
    {
        $this->_em->persist($customer);
        $this->_em->flush($customer);
    }
}
