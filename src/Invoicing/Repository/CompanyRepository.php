<?php

namespace Invoicing\Repository;

use Doctrine\ORM\EntityRepository;
use Invoicing\Entity\Company;
use Invoicing\Exception\CompanyNotFoundException;

class CompanyRepository extends EntityRepository
{
    public function get(int $companyId): Company
    {
        if ($company = $this->find($companyId)) {
            return $company;
        }

        throw new CompanyNotFoundException();
    }
}
