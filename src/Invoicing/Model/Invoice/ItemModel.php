<?php

namespace Invoicing\Model\Invoice;

use JMS\Serializer\Annotation as Serialize;
use Symfony\Component\Validator\Constraints as Assert;

class ItemModel
{
    /**
     * @Serialize\ReadOnly()
     * @Serialize\Type("integer")
     *
     * @var integer|null
     */
    private $id;

    /**
     * @Assert\Type("string")
     * @Assert\NotBlank()
     * @Serialize\Type("string")
     *
     * @var string
     */
    private $description;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("integer")
     * @Serialize\Type("integer")
     *
     * @var integer
     */
    private $amount;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("integer")
     * @Serialize\Type("integer")
     *
     * @var integer
     */
    private $price;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("integer")
     * @Serialize\Type("integer")
     *
     * @var integer
     */
    private $tax;

    /**
     * @param string       $description
     * @param integer      $amount
     * @param integer      $price
     * @param integer      $tax
     * @param integer|null $id
     */
    public function __construct($description, $amount, $price, $tax, $id = null)
    {
        $this->description = $description;
        $this->amount = $amount;
        $this->price = $price;
        $this->tax = $tax;
        $this->id = $id;
    }

    /**
     * @return integer|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return integer
     */
    public function getTax()
    {
        return $this->tax;
    }
}
