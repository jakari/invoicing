<?php

namespace Invoicing\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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
     * @parmm  int    $companyId
     * @param  string $name
     * @return Customer|null
     */
    public function getFirst(int $companyId, string $name)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(Customer::class, 'c');

        $query = $this->_em->createNativeQuery(
            "SELECT c.* FROM customer c WHERE c.name ILIKE CONCAT(:name::text, '%') AND c.company = :company ORDER BY c.name ASC LIMIT 1",
            $rsm
        );

        $results = $query
            ->setParameter(':name', $name)
            ->setParameter(':company', $companyId)
            ->getResult();

        return !empty($results) ? $results[0] : null;
    }
}
