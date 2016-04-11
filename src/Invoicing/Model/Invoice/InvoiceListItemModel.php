<?php

namespace Invoicing\Model\Invoice;

use JMS\Serializer\Annotation as Serialize;

class InvoiceListItemModel
{
    /**
     * @Serialize\Type("integer")
     * @var integer
     */
    private $id;

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
     * @Serialize\Type("string")
     * @var string
     */
    private $created;

    /**
     * @Serialize\Type("string")
     * @var string
     */
    private $due;

    /**
     * @Serialize\Type("integer")
     * @var integer
     */
    private $total;

    /**
     * @param integer $id
     * @param string  $customer
     * @param integer $invoiceNumber
     * @param integer $referenceNumber
     * @param string  $created
     * @param string  $due
     * @param integer $total
     */
    public function __construct(
        $id,
        $customer,
        $invoiceNumber,
        $referenceNumber,
        $created,
        $due,
        $total
    ) {
        $this->id = $id;
        $this->customer = $customer;
        $this->invoiceNumber = $invoiceNumber;
        $this->referenceNumber = $referenceNumber;
        $this->created = $created;
        $this->due = $due;
        $this->total = $total;
    }
}
