<?php

namespace Invoicing\Entity\Invoice;

use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use Invoicing\DBAL\Types\InvoiceStatusType;
use Invoicing\Entity\Company;
use Invoicing\Entity\Customer\Customer;
use Invoicing\Model\Invoice\CustomerModel;
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
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Invoicing\Entity\Company")
     * @ORM\JoinColumn(name="company", nullable=false)
     *
     * @var Company
     */
    private $company;

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
     * @ORM\Column(name="interest_on_arrears", type="integer", nullable=false)
     * @var int
     */
    private $interestOnArrears;

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
     * @ORM\Column(name="template", type="string", nullable=false)
     * @var string
     */
    private $template;

    /**
     * @ORM\Column(name="customer_name", type="string", nullable=false)
     * @var string
     */
    private $customerName;

    /**
     * @ORM\Column(name="customer_street_name", type="string", nullable=false)
     * @var string
     */
    private $customerStreetName;

    /**
     * @ORM\Column(name="customer_post_code", type="string", nullable=false)
     * @var string
     */
    private $customerPostCode;

    /**
     * @ORM\Column(name="customer_city", type="string", nullable=false)
     * @var string
     */
    private $customerCity;

    /**
     * @ORM\Column(name="customer_email", type="string", nullable=false)
     * @var string
     */
    private $customerEmail;

    /**
     * @ORM\Column(name="customer_phone", type="string", nullable=true)
     * @var string
     */
    private $customerPhone;

    /**
     * @ORM\Column(name="customer_vat", type="string", nullable=true)
     * @var string|null
     */
    private $customerVat;

    public function __construct(
        int $invoiceNumber,
        int $referenceNumber,
        \DateTime $created,
        \DateTime $due,
        Customer $customer,
        int $interestOnArrears,
        int $remarkingTime,
        string $template,
        string $customerReference = null,
        string $delivery = null,
        string $conditionsOfPayment = null
    ) {
        $this->invoiceNumber = $invoiceNumber;
        $this->referenceNumber = $referenceNumber;
        $this->created = $created;
        $this->due = $due;
        $this->customer = $customer;
        $this->company = $customer->getCompany();
        $this->customerName = $customer->getName();
        $this->customerStreetName = $customer->getStreetName();
        $this->customerCity = $customer->getCity();
        $this->customerPostCode = $customer->getPostCode();
        $this->customerEmail = $customer->getEmail();
        $this->customerPhone = $customer->getPhone();
        $this->customerVat = $customer->getVat();
        $this->status = InvoiceStatus::STATUS_PENDING;
        $this->items = new DoctrineCollection();
        $this->interestOnArrears = $interestOnArrears;
        $this->remarkingTime = $remarkingTime;
        $this->template = $template;
        $this->customerReference = $customerReference;
        $this->delivery = $delivery;
        $this->conditionsOfPayment = $conditionsOfPayment;
    }

    public static function createFromModel(
        Customer $customer,
        InvoiceModel $model,
        int $invoiceNumber,
        int $referenceNumber
    ) {
        return new self(
            $invoiceNumber,
            $referenceNumber,
            $model->getCreated(),
            $model->getDue(),
            $customer,
            $model->getInterestOnArrears(),
            $model->getRemarkingTime(),
            $model->getTemplate(),
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
            new CustomerModel(
                $this->customerName,
                $this->customerStreetName,
                $this->customerPostCode,
                $this->customerCity,
                $this->customerEmail,
                $this->customer->getId(),
                $this->customerVat,
                $this->customerPhone
            ),
            $this->items
                ->map(function (InvoiceItem $item) {
                    return $item->createOutputModel();
                })
                ->toArray(),
            $this->interestOnArrears,
            $this->remarkingTime,
            $this->template,
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
        $this->interestOnArrears = $model->getInterestOnArrears();
        $this->remarkingTime = $model->getRemarkingTime();
        $this->customerReference = $model->getCustomerReference();
        $this->delivery = $model->getDelivery();
        $this->conditionsOfPayment = $model->getConditionsOfPayment();
        $this->template = $model->getTemplate();

        $customer =  $model->getCustomer();
        $this->customerName = $customer->getName();
        $this->customerStreetName = $customer->getStreetName();
        $this->customerPostCode = $customer->getPostCode();
        $this->customerCity = $customer->getCity();
        $this->customerEmail = $customer->getEmail();
        $this->customerVat = $customer->getVat();
        $this->customerPhone = $customer->getPhone();

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
    public function getInterestOnArrears(): int
    {
        return $this->interestOnArrears;
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

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function getCustomerStreetName(): string
    {
        return $this->customerStreetName;
    }

    public function getCustomerPostCode(): string
    {
        return $this->customerPostCode;
    }

    public function getCustomerCity(): string
    {
        return $this->customerCity;
    }

    public function getCustomerVat(): ?string
    {
        return $this->customerVat;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getCustomerPhone(): ?string
    {
        return $this->customerPhone;
    }
}
