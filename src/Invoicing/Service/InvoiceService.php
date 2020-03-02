<?php

namespace Invoicing\Service;

use Invoicing\Database\Connection\Connection;
use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Exception\InvoiceNotFoundException;
use Invoicing\Exception\NoReferenceException;
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

    public function __construct(
        Connection $conn,
        CustomerService $customerService,
        ItemService $itemService,
        InvoiceRepository $repository,
        ReferenceCounter $counter,
        $defaultSettings
    ) {
        $this->conn = $conn;
        $this->customerService = $customerService;
        $this->itemService = $itemService;
        $this->repository = $repository;
        $this->referenceCounter = $counter;
        $this->defaultSettings = $defaultSettings;
    }

    /**
     * @param  InvoiceModel $model
     * @return integer
     */
    public function createInvoice(InvoiceModel $model)
    {
        return $this->repository->transactional(function () use ($model) {
            $customer = $this->customerService->saveCustomer($model->getCustomer());
            $invoice = Invoice::createFromModel($customer, $model);
            $this->repository->create($invoice);
            $reference = 100;

            try {
                $reference = (int) substr(
                    $this->repository->getNextReference(),
                    0,
                    -1
                );
                $reference++;
            } catch (NoReferenceException $e) {
            }

            $invoice->setReferenceNumber(
                $reference . $this->referenceCounter->checksum($reference)
            );
            $this->repository->update($invoice);

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
        return $this->repository->getAll();
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
        $this->customerService->removeCustomer($invoice->getCustomer());

        foreach ($invoice->getItems() as $item) {
            $this->itemService->removeItem($item);
        }

        $this->conn->prepare('DELETE FROM invoice WHERE invoice_number = :id')
            ->execute([':id' => $invoiceId]);
    }

    public function getSettings()
    {
        $settings = Yaml::parseFile($this->defaultSettings);

        return [
            'default' => $settings['default'],
            'templates' => $settings['templates']
        ];
    }
}
