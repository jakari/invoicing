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
     * @Assert\NotNull()
     * @Assert\Type("string")
     *
     * @var string
     */
    private $email;

    /**
     * @Serialize\Type("string")
     * @Assert\Type("string")
     *
     * @var string|null
     */
    private $vat;

    /**
     * @Serialize\Type("string")
     * @Assert\Type("string")
     *
     * @var string|null
     */
    private $phone;

    /**
     * @param string       $name
     * @param string       $streetName
     * @param string       $postCode
     * @param string       $city
     * @param string       $email
     * @param integer|null $id
     * @param string|null  $vat
     * @param string|null  $phone
     */
    public function __construct(
        $name,
        $streetName,
        $postCode,
        $city,
        $email,
        $id = null,
        $vat = null,
        $phone = null
    ) {
        $this->name = $name;
        $this->streetName = $streetName;
        $this->postCode = $postCode;
        $this->city = $city;
        $this->email = $email;
        $this->id = $id;
        $this->vat = $vat;
        $this->phone = $phone;
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

    public function getVat(): ?string
    {
        return $this->vat;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function shouldCreateNewCustomer()
    {
        return !$this->id;
    }
}
