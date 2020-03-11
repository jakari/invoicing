<?php

namespace Invoicing\Repository;

use Doctrine\ORM\EntityRepository;
use Invoicing\Entity\Company;
use Invoicing\Entity\Parameters;
use Invoicing\Exception\ParameterNotFoundException;

class ParameterRepository extends EntityRepository
{
    public function get(Company $company, string $key): string {
        $qb = $this->_em->createQueryBuilder();

        try {
            return $qb->select("p.value")
                ->from(Parameters::class, 'p')
                ->where('p.company = :company')
                ->andWhere('p.key = :key')
                ->setParameters(['key' => $key, 'company' => $company])
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            throw new ParameterNotFoundException($key);
        }
    }
}
