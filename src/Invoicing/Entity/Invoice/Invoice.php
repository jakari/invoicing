<?php

namespace Invoicing\Entity;

use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use Invoicing\DBAL\Types\InvoiceStatusType;
use Invoicing\Entity\Customer\Customer;
use Invoicing\Entity\Invoice\InvoiceItem;
use Invoicing\Value\InvoiceStatus;

/**
 * @ORM\Entity(repositoryClass="Invoicing\Repository\InvoiceRepository")
 * @ORM\Table(name="invoice")
 */
class Invoice
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(name="invoice_number", type="integer", nullable=false)
     *
     * @var integer
     */
    private $invoiceNumber;

    /**
     * @ORM\Column(name="reference_number", type="integer", nullable=false)
     *
     * @var integer
     */
    private $referenceNumber;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTimeInterface
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @var \DateTimeInterface
     */
    private $due;

    /**
     * @ORM\Column(type="invoice_status", nullable=false)
     *
     * @var InvoiceStatusType
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="Invoicing\Entity\Customer\Customer")
     * @ORM\JoinColumn(name="customer", referencedColumnName="id", nullable=false)
     *
     * @var Customer
     */
    private $customer;

    /**
     * @ORM\OneToMany(targetEntity="Invoicing\Entity\Invoice\InvoiceItem", mappedBy="invoice")
     *
     * @var DoctrineCollection
     */
    private $items;

    /**
     * @param integer            $invoiceNumber
     * @param integer            $referenceNumber
     * @param \DateTimeImmutable $created
     * @param \DateTimeImmutable $due
     * @param Customer           $customer
     */
    public function __construct(
        integer $invoiceNumber,
        integer $referenceNumber,
        \DateTimeImmutable $created,
        \DateTimeImmutable $due,
        Customer $customer
    ) {
        $this->invoiceNumber = $invoiceNumber;
        $this->referenceNumber = $referenceNumber;
        $this->created = $created;
        $this->due = $due;
        $this->customer = $customer;
        $this->status = InvoiceStatus::STATUS_PENDING;
    }

    /**
     * @param  InvoiceItem     $item
     * @throws \ErrorException
     */
    public function addItem(InvoiceItem $item) {
        if ($item->getInvoice() !== $this) {
            throw new \ErrorException('invalid invoice in InvoiceItem');
        }

        $this->addItem($item);
    }
}
