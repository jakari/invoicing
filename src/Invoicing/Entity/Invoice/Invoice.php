<?php

namespace Invoicing\Entity\Invoice;

use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use Invoicing\DBAL\Types\InvoiceStatusType;
use Invoicing\Entity\Customer\Customer;
use Invoicing\Model\Invoice\InvoiceModel;
use Invoicing\Model\Invoice\ItemModel;
use Invoicing\Value\InvoiceCalculator;
use Invoicing\Value\InvoiceStatus;
use Invoicing\Value\Money;
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
     * @ORM\Column(name="remarking_time", type="integer", nullable=false)
     * @var int
     */
    private $remarkingTime;

    /**
     * @ORM\Column(name="hesitation_cost_of_interest", type="integer", nullable=false)
     * @var int
     */
    private $hesitationCostOfInterest;

    /**
     * @ORM\Column(name="customer_reference", type="string", nullable=true)
     * @var string|null
     */
    private $customerReference;

    /**
     * @ORM\Column(name="delivery", type="string", nullable=true)
     * @var string|null
     */
    private $delivery;

    /**
     * @ORM\Column(name="conditions_of_payment", type="string", nullable=true)
     * @var string|null
     */
    private $conditionsOfPayment;

    /**
     * @param \DateTime   $created
     * @param \DateTime   $due
     * @param Customer    $customer
     * @param int         $hesitationCostOfInterest
     * @param int         $remarkingTime
     * @param string|null $customerReference
     * @param string|null $delivery
     * @param string|null $conditionsOfPayment
     */
    public function __construct(
        \DateTime $created,
        \DateTime $due,
        Customer $customer,
        int $hesitationCostOfInterest,
        int $remarkingTime,
        string $customerReference = null,
        string $delivery = null,
        string $conditionsOfPayment = null
    )
    {
        $this->created = $created;
        $this->due = $due;
        $this->customer = $customer;
        $this->status = InvoiceStatus::STATUS_PENDING;
        $this->items = new DoctrineCollection();
        $this->hesitationCostOfInterest = $hesitationCostOfInterest;
        $this->remarkingTime = $remarkingTime;
        $this->customerReference = $customerReference;
        $this->delivery = $delivery;
        $this->conditionsOfPayment = $conditionsOfPayment;
    }

    public static function createFromModel(
        Customer $customer,
        InvoiceModel $model
    )
    {
        return new self(
            $model->getCreated(),
            $model->getDue(),
            $customer,
            $model->getHesitationCostOfInterest(),
            $model->getRemarkingTime(),
            $model->getCustomerReference(),
            $model->getDelivery(),
            $model->getConditionsOfPayment()
        );
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
            $this->hesitationCostOfInterest,
            $this->remarkingTime,
            $this->invoiceNumber,
            $this->referenceNumber,
            $this->customerReference,
            $this->delivery,
            $this->conditionsOfPayment
        );
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function getTotal(): Money
    {
        return ArrayCollection::create($this->items)->reduce(
            function (Money $total, InvoiceItem $item) {
                return InvoiceCalculator::calculateItemTotal($item)->add($total);
            },
            new Money(0)
        );
    }

    public function getTaxTotal(): Money
    {
        return ArrayCollection::create($this->items)->reduce(
            function (Money $total, InvoiceItem $item) {
                return InvoiceCalculator::calculateItemTaxAmount($item)->add($total);
            },
            new Money(0)
        );
    }

    public function getTotalWithoutTax(): Money
    {
        return ArrayCollection::create($this->items)->reduce(
            function (Money $total, InvoiceItem $item) {
                return InvoiceCalculator::calculateItemTotalWithoutTax($item)->add($total);
            },
            new Money(0)
        );
    }

    public function updateFromModel(InvoiceModel $model)
    {
        $this->created = $model->getCreated();
        $this->due = $model->getDue();
        $this->hesitationCostOfInterest = $model->getHesitationCostOfInterest();
        $this->remarkingTime = $model->getRemarkingTime();
        $this->customerReference = $model->getCustomerReference();
        $this->delivery = $model->getDelivery();
        $this->conditionsOfPayment = $model->getConditionsOfPayment();

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

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @return int
     */
    public function getReferenceNumber(): int
    {
        return $this->referenceNumber;
    }

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @return \DateTime
     */
    public function getDue(): \DateTime
    {
        return $this->due;
    }

    /**
     * @return InvoiceItem[]
     */
    public function getItems(): array
    {
        return $this->items->toArray();
    }

    /**
     * @return int
     */
    public function getRemarkingTime(): int
    {
        return $this->remarkingTime;
    }

    /**
     * @return int
     */
    public function getHesitationCostOfInterest(): int
    {
        return $this->hesitationCostOfInterest;
    }

    /**
     * @return null|string
     */
    public function getCustomerReference(): ?string
    {
        return $this->customerReference;
    }

    /**
     * @return null|string
     */
    public function getDelivery(): ?string
    {
        return $this->delivery;
    }

    /**
     * @return null|string
     */
    public function getConditionsOfPayment(): ?string
    {
        return $this->conditionsOfPayment;
    }
}
