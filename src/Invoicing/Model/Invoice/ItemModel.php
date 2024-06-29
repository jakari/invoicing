<?php

namespace Invoicing\Model\Invoice;

use Invoicing\Value\Money;
use JMS\Serializer\Annotation as Serialize;
use Symfony\Component\Validator\Constraints as Assert;

class ItemModel
{
    /**
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
     * @Assert\Type("Invoicing\Value\Money")
     * @Serialize\Type("Invoicing\Value\Money")
     *
     * @var Money
     */
    private $price;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Serialize\Type("string")
     *
     * @var string
     */
    private $tax;

    public function __construct(
        string $description,
        int $amount,
        Money $price,
        string $tax,
        int $id = null
    ) {
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
    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getTax(): string
    {
        return $this->tax;
    }
}
