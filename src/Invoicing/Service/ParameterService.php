<?php

namespace Invoicing\Service;

use Invoicing\Repository\ParameterRepository;

class ParameterService
{
    private $currentCompanyService;

    private $repository;

    public function __construct(CurrentCompanyService $currentCompanyService, ParameterRepository $repository)
    {
        $this->currentCompanyService = $currentCompanyService;
        $this->repository = $repository;
    }

    public function getInitialInvoiceNumber()
    {
        return $this->repository->get(
            $this->currentCompanyService->get(),
            'initial_invoice_number'
        );
    }

    public function getInitialReferenceNumber()
    {
        return $this->repository->get(
            $this->currentCompanyService->get(),
            'initial_reference_number'
        );
    }
}
