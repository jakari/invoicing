<?php

namespace Invoicing\Repository;

use Doctrine\ORM\EntityRepository;
use Invoicing\Entity\Customer\Customer;
use Invoicing\Exception\CustomerNotFoundException;

class CustomerRepository extends EntityRepository
{
    /**
     * @param  int                       $id
     * @return Customer
     * @throws CustomerNotFoundException
     */
    public function get(int $id): Customer
    {
        /** @var Customer $customer */
        if ($customer = $this->find($id)) {
            return $customer;
        }

        throw new CustomerNotFoundException($id);
    }

    public function save(Customer $customer)
    {
        $this->_em->persist($customer);
        $this->_em->flush($customer);
    }

    public function update(Customer $customer)
    {
        $this->_em->flush($customer);
    }

    /**
     * @param $name
     * @return Customer|null
     */
    public function getFirst($name)
    {
        $customer = Customer::class;
        $results = $this->_em->createQuery("SELECT c FROM {$customer} c WHERE c.name LIKE CONCAT(:name, '%') ORDER BY c.name ASC")
            ->setMaxResults(1)
            ->setParameter(':name', $name)
            ->getResult();

        return !empty($results) ? $results[0] : null;
    }
}