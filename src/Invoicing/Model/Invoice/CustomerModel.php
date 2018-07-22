<?php

namespace Invoicing\Model\Invoice;

use JMS\Serializer\Annotation as Serialize;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerModel
{
    /**
     * @Serialize\Type("integer")
     * @Assert\Type("integer")
     * @var integer|null
     */
    private $id;
    
    /**
     * @Serialize\Type("string")
     * @Assert\NotBlank()
     * @Assert\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @Serialize\SerializedName("streetName")
     * @Serialize\Type("string")
     * @Assert\NotBlank()
     * @Assert\Type("string")
     *
     * @var string
     */
    private $streetName;

    /**
     * @Serialize\SerializedName("postCode")
     * @Serialize\Type("string")
     * @Assert\NotBlank()
     * @Assert\Type("string")
     *
     * @var string
     */
    private $postCode;

    /**
     * @Serialize\Type("string")
     * @Assert\NotBlank()
     * @Assert\Type("string")
     *
     * @var string
     */
    private $city;

    /**
     * @Serialize\Type("string")
     * @Assert\NotBlank()
     * @Assert\Type("string")
     *
     * @var string
     */
    private $email;

    /**
     * @param string       $name
     * @param string       $streetName
     * @param string       $postCode
     * @param string       $city
     * @param string       $email
     * @param integer|null $id
     */
    public function __construct(
        $name,
        $streetName,
        $postCode,
        $city,
        $email,
        $id
    ) {
        $this->name = $name;
        $this->streetName = $streetName;
        $this->postCode = $postCode;
        $this->city = $city;
        $this->email = $email;
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getStreetName()
    {
        return $this->streetName;
    }

    /**
     * @return string
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function shouldCreateNewCustomer()
    {
        return !$this->id;
    }
}
