<?php

namespace Invoicing\Model\Invoice;

use JMS\Serializer\Annotation as Serialize;
use Symfony\Component\Validator\Constraints as Assert;

class InvoiceModel
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type("DateTime")
     * @Serialize\Type("DateTime<'Y-m-d'>")
     *
     * @var \DateTime
     */
    private $created;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("DateTime")
     * @Serialize\Type("DateTime<'Y-m-d'>")
     *
     * @var \DateTime
     */
    private $due;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("Invoicing\Model\Invoice\CustomerModel")
     * @Serialize\Type("Invoicing\Model\Invoice\CustomerModel")
     *
     * @var CustomerModel
     */
    private $customer;

    /**
     * @Serialize\ReadOnly()
     * @Serialize\SerializedName("invoiceNumber")
     * @Serialize\Type("integer")
     * @var integer|null
     */
    private $invoiceNumber;

    /**
     * @Serialize\ReadOnly()
     * @Serialize\SerializedName("referenceNumber")
     * @Serialize\Type("integer")
     * @var integer|null
     */
    private $referenceNumber;

    /**
     * @Assert\NotBlank()
     * @Assert\All({
     * @Assert\Type("Invoicing\Model\Invoice\ItemModel")
     * })
     * @Assert\Count(min=1)
     * @Serialize\Type("array<Invoicing\Model\Invoice\ItemModel>")
     *
     * @var ItemModel[]
     */
    private $items;

    /**
     * @Serialize\SerializedName("remarkingTime")
     * @Serialize\Type("integer")
     * @Assert\NotBlank()
     * @Assert\Type("integer")
     * @Assert\Range(min=0, max=360)
     * @var int
     */
    private $remarkingTime;

    /**
     * @Serialize\SerializedName("interestOnArrears")
     * @Serialize\Type("integer")
     * @Assert\NotBlank()
     * @Assert\Type("integer")
     * @Assert\Range(min=0, max=100)
     * @var int
     */
    private $interestOnArrears;

    /**
     * @Serialize\SerializedName("customerReference")
     * @Serialize\Type("string")
     * @Assert\Type("string")
     * @Assert\Length(min=1, max=255)
     * @var string|null
     */
    private $customerReference;

    /**
     * @Serialize\Type("string")
     * @Assert\Type("string")
     * @Assert\Length(min=1, max=255)
     * @var string|null
     */
    private $delivery;

    /**
     * @Serialize\SerializedName("conditionsOfPayment")
     * @Serialize\Type("string")
     * @Assert\Type("string")
     * @Assert\Length(min=1, max=255)
     * @var string|null
     */
    private $conditionsOfPayment;

    /**
     * @Serialize\SerializedName("template")
     * @Assert\NotBlank()
     * @Serialize\Type("string")
     * @Assert\Type("string")
     * @Assert\Length(min=1, max=255)
     * @var string
     */
    private $template;

    /**
     * @param \DateTime     $created
     * @param \DateTime     $due
     * @param CustomerModel $customer
     * @param ItemModel[]   $items
     * @param int           $interestOnArrears
     * @param int           $remarkingTime
     * @param string        $template
     * @param integer|null  $invoiceNumber
     * @param integer|null  $referenceNumber
     * @param string|null   $customerReference
     * @param string|null   $delivery
     * @param string|null   $conditionsOfPayment
     */
    public function __construct(
        \DateTime $created,
        \DateTime $due,
        CustomerModel $customer,
        array $items,
        int $interestOnArrears,
        int $remarkingTime,
        string $template,
        $invoiceNumber = null,
        $referenceNumber = null,
        string $customerReference = null,
        string $delivery = null,
        string $conditionsOfPayment = null
    ) {
        $this->created = $created;
        $this->due = $due;
        $this->customer = $customer;
        $this->items = $items;
        $this->interestOnArrears = $interestOnArrears;
        $this->remarkingTime = $remarkingTime;
        $this->template = $template;
        $this->invoiceNumber = $invoiceNumber;
        $this->referenceNumber = $referenceNumber;
        $this->customerReference = $customerReference;
        $this->delivery = $delivery;
        $this->conditionsOfPayment = $conditionsOfPayment;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return \DateTime
     */
    public function getDue()
    {
        return $this->due;
    }

    /**
     * @return CustomerModel
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return ItemModel[]
     */
    public function getItems()
    {
        return $this->items;
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
     * @return string[null
     */
    public function getCustomerReference(): ?string
    {
        return $this->customerReference;
    }

    /**
     * @return string[null
     */
    public function getDelivery(): ?string
    {
        return $this->delivery;
    }

    /**
     * @return string[null
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
}
