<?php

namespace Invoicing\Service;

use Invoicing\Database\Connection\Connection;
use Invoicing\Exception\InvoiceNotFoundException;
use Invoicing\Model\Invoice\CustomerModel;
use Invoicing\Model\Invoice\InvoiceListItemModel;
use Invoicing\Model\Invoice\InvoiceModel;
use Invoicing\Value\InvoiceStatus;

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
     * @param Connection $conn
     */
    public function __construct(
        Connection $conn,
        CustomerService $customerService
    ) {
        $this->conn = $conn;
        $this->customerService = $customerService;
    }

    public function createInvoice(InvoiceModel $model)
    {
        // Todo add items

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
            [],
            $invoiceData['id']
        );
    }
}
