<?php

namespace Invoicing\Model\Invoice;

use JMS\Serializer\Annotation as Serialize;
use Symfony\Component\Validator\Constraints as Assert;

class ItemModel
{
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
}
