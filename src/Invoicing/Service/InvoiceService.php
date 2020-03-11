<?php

namespace Invoicing\Service;

use Invoicing\Database\Connection\Connection;
use Invoicing\Entity\Company;
use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Exception\CompanyNotFoundException;
use Invoicing\Exception\CompanyNotSelectedException;
use Invoicing\Exception\InvoiceNotFoundException;
use Invoicing\Exception\NoValueException;
use Invoicing\Model\Invoice\InvoiceListItemModel;
use Invoicing\Model\Invoice\InvoiceModel;
use Invoicing\Repository\InvoiceRepository;
use Invoicing\Value\ReferenceCounter;
use Symfony\Component\Yaml\Yaml;

class InvoiceService
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var ItemService
     */
    private $itemService;

    /**
     * @var InvoiceRepository
     */
    private $repository;

    /**
     * @var ReferenceCounter
     */
    private $referenceCounter;

    /**
     * @var string
     */
    private $defaultSettings;

    /**
     * @var CurrentCompanyService
     */
    private $currentCompanyService;

    private $parameterService;

    public function __construct(
        Connection $conn,
        CustomerService $customerService,
        ItemService $itemService,
        InvoiceRepository $repository,
        ReferenceCounter $counter,
        $defaultSettings,
        CurrentCompanyService $currentCompanyService,
        ParameterService $parameterService
    ) {
        $this->conn = $conn;
        $this->customerService = $customerService;
        $this->itemService = $itemService;
        $this->repository = $repository;
        $this->referenceCounter = $counter;
        $this->defaultSettings = $defaultSettings;
        $this->currentCompanyService = $currentCompanyService;
        $this->parameterService = $parameterService;
    }

    /**
     * @param  InvoiceModel $model
     * @return integer
     */
    public function createInvoice(InvoiceModel $model)
    {
        $company = $this->currentCompanyService->get();

        return $this->repository->transactional(function () use ($model, $company) {
            $customer = $this->customerService->saveCustomer($model->getCustomer());

            $reference = null;
            $invoiceNumber = null;

            try {
                $reference = 1 + (int) substr(
                        $this->repository->getMaxReference($company),
                        0,
                        -1
                    );
            } catch (NoValueException $e) {
                $reference = $this->parameterService->getInitialReferenceNumber();
            }
            try {
                $invoiceNumber = $this->repository->getMaxInvoiceNumber($company) + 1;
            } catch (NoValueException $e) {
                $invoiceNumber = $this->parameterService->getInitialInvoiceNumber();
            }

            $invoice = Invoice::createFromModel($company, $customer, $model, $invoiceNumber, $reference);
            $this->repository->create($invoice);

            foreach ($model->getItems() as $item) {
                $this->itemService->addItem($invoice, $item);
            }
            $this->itemService->flush();

            return $invoice->getInvoiceNumber();
        });
    }

    /**
     * @param integer      $id
     * @param InvoiceModel $model
     */
    public function updateInvoice($id, InvoiceModel $model)
    {
        $this->repository->transactional(function () use ($id, $model) {
            $invoice = $this->repository->get($id);
            // Update customer
            $customer = $this->customerService
                ->saveCustomer($model->getCustomer());
            $invoice->updateFromModel($model);
            $invoice->setCustomer($customer);

            $this->repository->update($invoice);
        });
    }

    /**
     * @return InvoiceListItemModel[]
     */
    public function getInvoices()
    {
        return $this->repository->getAll($this->currentCompanyService->get());
    }

    /**
     * @param  integer                  $invoiceId
     * @return InvoiceModel
     * @throws InvoiceNotFoundException
     */
    public function getInvoice($invoiceId)
    {
        return $this->repository->get($invoiceId)->createOutputModel();
    }

    /**
     * @param integer $invoiceId
     */
    public function remove($invoiceId)
    {
        $invoice = $this->getInvoice($invoiceId);

        foreach ($invoice->getItems() as $item) {
            $this->itemService->removeItem($item);
        }

        $this->conn->prepare('DELETE FROM invoice WHERE invoice_number = :id')
            ->execute([':id' => $invoiceId]);
    }

    public function getSettings()
    {
        $settings = Yaml::parseFile($this->defaultSettings);

        $selectedCompany = null;

        try {
            $selectedCompany = $this->currentCompanyService->get();
        } catch (CompanyNotSelectedException $e) {}

        return [
            'default' => $settings['default'],
            'templates' => $settings['templates'],
            'companies' => array_map(
                function (Company $comapny) {
                    return [
                        'id' => $comapny->getId(),
                        'name' => $comapny->getName()
                    ];
                },
                $this->currentCompanyService->getCompanies()
            ),
            'selected_company' => $selectedCompany
        ];
    }
}
