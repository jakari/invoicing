<?php

namespace Invoicing\Repository;

use Doctrine\ORM\EntityRepository;
use Invoicing\Entity\Company;
use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Exception\InvoiceNotFoundException;
use Invoicing\Exception\NoValueException;
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
     * @param  Company $company
     * @return InvoiceListItemModel[]
     */
    public function getAll(Company $company)
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
            WHERE invoice.company = :company
            GROUP BY invoice.id, customer.id
            ORDER BY created DESC, invoice_number DESC
        ');
        $stmt->execute([':company' => $company->getId()]);

        return array_map(function (array $invoice) {
            return new InvoiceListItemModel(
                $invoice['customer'],
                $invoice['invoice_number'],
                $invoice['reference_number'],
                new \DateTime($invoice['created']),
                new \DateTime($invoice['due']),
                new Money($invoice['total'])
            );
        }, $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function get($invoiceNumber)
    {
        /** @var Invoice $invoice */
        if ($invoice = $this->findOneBy(['invoiceNumber' => $invoiceNumber])) {
            return $invoice;
        }


        throw new InvoiceNotFoundException();
    }

    /**
     * @throws NoValueException
     */
    public function getMaxReference(Company $company) : int
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('MAX(i.referenceNumber)')
            ->from(Invoice::class, 'i')
            ->where('i.company = :company')
            ->setParameter(':company', $company);

        $result = $qb->getQuery()->getSingleScalarResult();

        if (!$result) {
            throw new NoValueException();
        }

        return $result;
    }

    /**
     * @throws NoValueException
     */
    public function getMaxInvoiceNumber(Company $company) : int
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('MAX(i.invoiceNumber)')
            ->from(Invoice::class, 'i')
            ->where('i.company = :company')
            ->setParameter(':company', $company);

        $result = $qb->getQuery()->getSingleScalarResult();

        if (!$result) {
            throw new NoValueException();
        }

        return $result;
    }
}
