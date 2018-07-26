<?php

namespace Invoicing\Repository;

use Doctrine\ORM\EntityRepository;
use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Exception\InvoiceNotFoundException;
use Invoicing\Exception\NoReferenceException;
use Invoicing\Model\Invoice\InvoiceListItemModel;
use Invoicing\Value\Money;

class InvoiceRepository extends EntityRepository
{
    public function transactional(callable $callback) {
        return $this->_em->transactional($callback);
    }

    public function create(Invoice $invoice)
    {
        $this->_em->persist($invoice);
    }

    public function update(Invoice $invoice)
    {
        $this->_em->flush();
    }

    /**
     * @return InvoiceListItemModel[]
     */
    public function getAll()
    {
        $conn = $this->_em->getConnection();

        $stmt = $conn->prepare('
            SELECT
                invoice_number,
                customer.name AS customer,
                reference_number,
                created,
                due,
                status,
                COALESCE(SUM(item.amount * (1 + (item.tax / 100)) * item.price), 0) AS total
            FROM invoice
            JOIN customer ON invoice.customer = customer.id
            LEFT JOIN invoice_item item ON invoice.invoice_number = item.invoice
            GROUP BY invoice.invoice_number, customer.id
            ORDER BY created DESC, invoice_number DESC
        ');
        $stmt->execute();

        return array_map(function (array $invoice) {
            return new InvoiceListItemModel(
                $invoice['customer'],
                $invoice['invoice_number'],
                $invoice['reference_number'],
                $invoice['created'],
                $invoice['due'],
                new Money($invoice['total'])
            );
        }, $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function get($id)
    {
        /** @var Invoice $invoice */
        if ($invoice = $this->find($id)) {
            return $invoice;
        }

        throw new InvoiceNotFoundException();
    }

    /**
     * @throws NoReferenceException
     */
    public function getNextReference() : int
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('MAX(i.referenceNumber)')
            ->from(Invoice::class, 'i');

        $result = $qb->getQuery()->getSingleScalarResult();

        if (!$result) {
            throw new NoReferenceException();
        }

        return $result;
    }
}
