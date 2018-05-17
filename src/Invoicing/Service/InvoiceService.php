<?php

namespace Invoicing\Service;

use Invoicing\Database\Connection\Connection;
use Invoicing\Exception\InvoiceNotFoundException;
use Invoicing\Model\Invoice\CustomerModel;
use Invoicing\Model\Invoice\InvoiceListItemModel;
use Invoicing\Model\Invoice\InvoiceModel;
use Invoicing\Model\Invoice\ItemModel;
use Invoicing\Value\InvoiceStatus;
use Xi\Collections\Collection\ArrayCollection;

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

    public function __construct(
        Connection $conn,
        CustomerService $customerService,
        ItemService $itemService
    ) {
        $this->conn = $conn;
        $this->customerService = $customerService;
        $this->itemService = $itemService;
    }

    /**
     * @param  InvoiceModel $model
     * @return integer
     */
    public function createInvoice(InvoiceModel $model)
    {
        $customer = $model->getCustomer();
        $this->customerService->createCustomer($customer);

        $stmt = $this->conn->prepare(
            'INSERT INTO invoice(reference_number, created, due, customer, status) VALUES(:reference_number, :created, :due, :customer, :status)'
        );
        $stmt->execute([
            // Todo calculate reference number automatically
            ':reference_number' => rand(1, 100000),
            ':created' => $model->getCreated()->format('Y-m-d'),
            ':due' => $model->getDue()->format('Y-m-d'),
            ':customer' => $customer->getId(),
            ':status' => InvoiceStatus::STATUS_PENDING
        ]);

        $invoiceId = (integer) $this->conn->lastInsertId('invoice_id_seq');

        var_dump($stmt->errorCode());
        var_dump($stmt->errorInfo());

        foreach ($model->getItems() as $item) {
            $this->itemService->addItem($invoiceId, $item);
        }
    }

    /**
     * @param integer      $id
     * @param InvoiceModel $model
     */
    public function updateInvoice($id, InvoiceModel $model)
    {
        $stmt = $this->conn->prepare('UPDATE invoice
            SET created = :created, due = :due');
        $stmt->execute([
            ':created' => $model->getCreated()->format('Y-m-d'),
            'due' => $model->getDue()->format('Y-m-d')
        ]);

        // Update customer
        $this->customerService->updateCustomer($model->getCustomer());

        $updatedItems = ArrayCollection::create($model->getItems());
        $itemsToUpdate = $updatedItems
            ->filter(function (ItemModel $model) {
                return $model->getId() !== null;
            });
        $itemIdsToKeep = $itemsToUpdate->map(function (ItemModel $model) {
                return $model->getId();
            })
            ->toArray();

        $currentItems = $this->itemService->getItems($id);

        // Remove items that doesn't exist anymore in the incoming array
        ArrayCollection::create($currentItems)
            ->filter(function (ItemModel $item) use ($itemIdsToKeep) {
                return !in_array($item->getId(), $itemIdsToKeep);
            })
            ->each(function (ItemModel $item) {
                $this->itemService->removeItem($item);
            });

        // Create items that don't have an id yet
        $updatedItems
            ->filter(function (ItemModel $item) {
                return $item->getId() === null;
            })
            ->each(function (ItemModel $item) use ($id) {
                $this->itemService->addItem($id, $item);
            });

        // Finally update the existing items
        $itemsToUpdate->each(function (ItemModel $item) {
            $this->itemService->updateItem($item);
        });
    }

    /**
     * @return InvoiceListItemModel[]
     */
    public function getInvoices()
    {
        $stmt = $this->conn->prepare('
            SELECT
                invoice.id,
                customer.name AS customer,
                invoice_number,
                reference_number,
                created,
                due,
                status,
                COALESCE(SUM(item.amount * (1 + (item.tax / 100)) * item.price), 0) AS total
            FROM invoice
            JOIN customer ON invoice.customer = customer.id
            LEFT JOIN item ON invoice.id = item.invoice
            GROUP BY invoice.id, customer.id
            ORDER BY created DESC
        ');
        $stmt->execute();

        return array_map(function (array $invoice) {
            return new InvoiceListItemModel(
                $invoice['id'],
                $invoice['customer'],
                $invoice['invoice_number'],
                $invoice['reference_number'],
                $invoice['created'],
                $invoice['due'],
                $invoice['total']
            );
        }, $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * @param  integer                  $invoiceId
     * @return InvoiceModel
     * @throws InvoiceNotFoundException
     */
    public function getInvoice($invoiceId)
    {
        $stmt = $this->conn->prepare(
            'SELECT
             invoice.id,
             invoice_number,
             reference_number,
             created,
             due,
             customer.id AS customerid,
             status,
             name,
             street_name,
             post_code,
             city,
             email
             FROM invoice JOIN customer ON invoice.customer = customer.id WHERE invoice.id = :invoice'
        );
        $stmt->execute([':invoice' => $invoiceId]);
        $invoiceData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$invoiceData) {
            throw new InvoiceNotFoundException(sprintf('Invoice "%d" not found.', $invoiceId));
        }

        return new InvoiceModel(
            new \DateTime($invoiceData['created']),
            new \DateTime($invoiceData['due']),
            new CustomerModel(
                $invoiceData['name'],
                $invoiceData['street_name'],
                $invoiceData['post_code'],
                $invoiceData['city'],
                $invoiceData['email'],
                $invoiceData['customerid']
            ),
            $invoiceData['invoice_number'],
            $invoiceData['reference_number'],
            $this->itemService->getItems($invoiceId),
            $invoiceData['id']
        );
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

        $this->conn->prepare('DELETE FROM invoice WHERE id = :id')
            ->execute([':id' => $invoiceId]);
    }
}
