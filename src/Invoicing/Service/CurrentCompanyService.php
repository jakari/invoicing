<?php

namespace Invoicing\Service;

use Invoicing\Entity\Company;
use Invoicing\Exception\CompanyNotSelectedException;
use Invoicing\Repository\CompanyRepository;
use Symfony\Component\HttpFoundation\Session\Session;

class CurrentCompanyService
{
    private $repository;

    private $session;

    public function __construct(CompanyRepository $companyRepository, Session $session)
    {
        $this->repository = $companyRepository;
        $this->session = $session;
    }

    public function set(int $companyId)
    {
        $this->session->set('current_company', $companyId);
    }

    public function get(): Company
    {
        $id = $this->session->get('current_company');

        if (!$id) {
            throw new CompanyNotSelectedException();
        }

        return $this->repository->get($id);
    }
    public function getId(): int {
        return (int) $this->session->get('current_company');
    }

    public function getCompanies(): array
    {
        return $this->repository->findAll();
    }
}
