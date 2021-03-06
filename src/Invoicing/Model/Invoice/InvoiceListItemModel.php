<?php

namespace Invoicing\Model\Invoice;

use Invoicing\Value\Money;
use JMS\Serializer\Annotation as Serialize;

class InvoiceListItemModel
{
    /**
     * @Serialize\Type("string")
     * @var string
     */
    private $customer;

    /**
     * @Serialize\Type("integer")
     * @Serialize\SerializedName("invoiceNumber")
     * @var integer
     */
    private $invoiceNumber;

    /**
     * @Serialize\Type("integer")
     * @Serialize\SerializedName("referenceNumber")
     * @var integer
     */
    private $referenceNumber;

    /**
     * @Serialize\Type("DateTime<'d.m.Y'>")
     * @var \DateTime
     */
    private $created;

    /**
     * @Serialize\Type("DateTime<'d.m.Y'>")
     * @var \DateTime
     */
    private $due;

    /**
     * @Serialize\Type("Invoicing\Value\Money")
     * @var Money
     */
    private $total;

    /**
     * @param string  $customer
     * @param integer $invoiceNumber
     * @param integer $referenceNumber
     * @param \DateTime $created
     * @param \DateTime $due
     * @param Money   $total
     */
    public function __construct(
        $customer,
        $invoiceNumber,
        $referenceNumber,
        \DateTime $created,
        \DateTime $due,
        Money $total
    ) {
        $this->customer = $customer;
        $this->invoiceNumber = $invoiceNumber;
        $this->referenceNumber = $referenceNumber;
        $this->created = $created;
        $this->due = $due;
        $this->total = $total;
    }
}
