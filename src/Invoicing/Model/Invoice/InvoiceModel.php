<?php

namespace Invoicing\Model\Invoice;

use JMS\Serializer\Annotation as Serialize;
use Symfony\Component\Validator\Constraints as Assert;

class InvoiceModel
{
    /**
     * @Serialize\Type("integer")
     * @var integer|null
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("DateTime")
     *
     * @var \DateTime
     */
    private $created;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("DateTime")
     *
     * @var \DateTime
     */
    private $due;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("Invoicing\Model\Invoice\Customer")
     *
     * @var \DateTime
     */
    private $customer;

    /**
     * @param \DateTimeInterface $created
     * @param \DateTimeInterface $due
     * @param CustomerModel      $customer
     * @param string|null        $id
     */
    public function __construct(
        \DateTimeInterface $created,
        \DateTimeInterface $due,
        CustomerModel $customer,
        $id = null
    ) {
        $this->created = $created;
        $this->due = $due;
        $this->customer = $customer;
        $this->id = $id;
    }
}
