<?php

namespace Invoicing\Entity\Invoice;

use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use Invoicing\DBAL\Types\InvoiceStatusType;
use Invoicing\Entity\Customer\Customer;
use Invoicing\Model\Invoice\InvoiceModel;
use Invoicing\Value\InvoiceStatus;

/**
 * @ORM\Entity(repositoryClass="Invoicing\Repository\InvoiceRepository")
 * @ORM\Table(name="invoice")
 */
class Invoice
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="invoice_number")
     * @ORM\GeneratedValue
     *
     * @var integer
     */
    private $invoiceNumber;

    /**
     * @ORM\Column(name="reference_number", type="integer", nullable=true)
     *
     * @var integer
     */
    private $referenceNumber;

    /**
     * @ORM\Column(type="date", nullable=false)
     *
     * @var \DateTime
     */
    private $created;

    /**
     * @ORM\Column(type="date", nullable=false)
     *
     * @var \DateTime
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
     * @param \DateTime $created
     * @param \DateTime $due
     * @param Customer  $customer
     */
    public function __construct(
        \DateTime $created,
        \DateTime $due,
        Customer $customer
    ) {
        $this->created = $created;
        $this->due = $due;
        $this->customer = $customer;
        $this->status = InvoiceStatus::STATUS_PENDING;
    }

    public static function createFromModel(
        Customer $customer,
        InvoiceModel $model
    ) {
        return new self($model->getCreated(), $model->getDue(), $customer);
    }

    public function getInvoiceNumber() : int {
        return $this->invoiceNumber;
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

    public function setReferenceNumber(int $number)
    {
        $this->referenceNumber = $number;
    }
}
