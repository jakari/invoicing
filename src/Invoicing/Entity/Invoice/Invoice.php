<?php

namespace Invoicing\Entity\Invoice;

use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use Invoicing\DBAL\Types\InvoiceStatusType;
use Invoicing\Entity\Customer\Customer;
use Invoicing\Model\Invoice\InvoiceListItemModel;
use Invoicing\Model\Invoice\InvoiceModel;
use Invoicing\Model\Invoice\ItemModel;
use Invoicing\Value\InvoiceStatus;
use Xi\Collections\Collection\ArrayCollection;

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
     * @ORM\OneToMany(targetEntity="Invoicing\Entity\Invoice\InvoiceItem", mappedBy="invoice", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"id": "ASC"})
     *
     * @var DoctrineCollection
     */
    private $items;

    /**
     * @param \DateTime $created
     * @param \DateTime $due
     * @param Customer $customer
     */
    public function __construct(
        \DateTime $created,
        \DateTime $due,
        Customer $customer
    )
    {
        $this->created = $created;
        $this->due = $due;
        $this->customer = $customer;
        $this->status = InvoiceStatus::STATUS_PENDING;
        $this->items = new DoctrineCollection();
    }

    public static function createFromModel(
        Customer $customer,
        InvoiceModel $model
    )
    {
        return new self($model->getCreated(), $model->getDue(), $customer);
    }

    public function getInvoiceNumber(): int
    {
        return $this->invoiceNumber;
    }

    /**
     * @param  InvoiceItem $item
     * @throws \ErrorException
     */
    public function addItem(InvoiceItem $item)
    {
        if ($item->getInvoice() !== $this) {
            throw new \ErrorException('invalid invoice in InvoiceItem');
        }

        $this->items->add($item);
    }

    public function setReferenceNumber(int $number)
    {
        $this->referenceNumber = $number;
    }

    public function createOutputModel()
    {
        return new InvoiceModel(
            $this->created,
            $this->due,
            $this->customer->createOutputModel(),
            $this->items
                ->map(function (InvoiceItem $item) {
                    return $item->createOutputModel();
                })
                ->toArray(),
            $this->invoiceNumber,
            $this->referenceNumber
        );
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function updateFromModel(InvoiceModel $model)
    {
        $this->created = $model->getCreated();
        $this->due = $model->getDue();

        $updatedItems = ArrayCollection::create($model->getItems());
        $itemsToUpdate = $updatedItems
            ->filter(function (ItemModel $model) {
                return $model->getId() !== null;
            });
        $itemIdsToKeep = $itemsToUpdate
            ->map(function (ItemModel $model) {
                return $model->getId();
            })
            ->toArray();

        // Remove items that doesn't exist anymore in the incoming array
        $this->items
            ->filter(function (InvoiceItem $item) use ($itemIdsToKeep) {
                return !in_array($item->getId(), $itemIdsToKeep);
            })
            ->forAll(function ($key, InvoiceItem $item) {
                $this->items->removeElement($item);
                return true;
            });

        // Create items that don't have an id yet
        $updatedItems
            ->filter(function (ItemModel $item) {
                return $item->getId() === null;
            })
            ->each(function (ItemModel $model) {
                $this->items->add(InvoiceItem::createFromModel($this, $model));
            });

        // Finally update the existing items
        $itemsToUpdate->each(function (ItemModel $model) {
            /** @var InvoiceItem $item */
            $item = $this->items
                ->filter(function (InvoiceItem $item) use ($model) {
                    return $item->getId() === $model->getId();
                })
                ->first();

            $item->updateFromModel($model);
        });
    }
}
