<?php

namespace Invoicing\Model\Invoice;

use JMS\Serializer\Annotation as Serialize;
use Symfony\Component\Validator\Constraints as Assert;

class InvoiceModel
{
    /**
     * @Assert\Type("integer")
     * @Serialize\Type("integer")
     * @var integer|null
     */
    private $id;

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
     * @Serialize\Type("array<Invoicing\Model\Invoice\ItemModel>")
     *
     * @var ItemModel[]
     */
    private $items;

    /**
     * @param \DateTime          $created
     * @param \DateTime          $due
     * @param CustomerModel      $customer
     * @param integer            $invoiceNumber
     * @param integer            $referenceNumber
     * @param ItemModel[]        $items
     * @param string|null        $id
     */
    public function __construct(
        \DateTime $created,
        \DateTime $due,
        CustomerModel $customer,
        $invoiceNumber,
        $referenceNumber,
        array $items,
        $id = null
    ) {
        $this->created = $created;
        $this->due = $due;
        $this->customer = $customer;
        $this->invoiceNumber = $invoiceNumber;
        $this->referenceNumber = $referenceNumber;
        $this->items = $items;
        $this->id = $id;
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
}
