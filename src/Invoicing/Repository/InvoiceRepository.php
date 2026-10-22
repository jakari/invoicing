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

    public function remove(Invoice $invoice)
    {
        // orphanRemoval on Invoice::$items removes the items too
        $this->_em->remove($invoice);
        $this->_em->flush();
    }

    /**
     * Serializes invoice creation per company, so concurrent invoices don't get the same number.
     */
    public function lockCompany(Company $company)
    {
        $this->_em->getConnection()->executeQuery(
            'SELECT id FROM company WHERE id = :company FOR UPDATE',
            ['company' => $company->getId()]
        );
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
                -- Rounds the tax of each item like InvoiceCalculator does for the printed invoice
                COALESCE(SUM(item.amount * item.price + ROUND(item.amount * item.price * item.tax / 100)), 0) AS total
            FROM invoice
            JOIN customer ON invoice.customer = customer.id
            LEFT JOIN invoice_item item ON invoice.id = item.invoice
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
                new Money((int) $invoice['total'])
            );
        }, $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * Invoice numbers run per company, so the same number exists in several companies.
     *
     * @throws InvoiceNotFoundException
     */
    public function get(Company $company, $invoiceNumber): Invoice
    {
        /** @var Invoice $invoice */
        if ($invoice = $this->findOneBy(['company' => $company, 'invoiceNumber' => $invoiceNumber])) {
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
