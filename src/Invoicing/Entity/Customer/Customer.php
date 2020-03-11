<?php

namespace Invoicing\Entity\Customer;

use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;
use Invoicing\Entity\Company;
use Invoicing\Model\Invoice\CustomerModel;

/**
 * @Entity(repositoryClass="Invoicing\Repository\CustomerRepository")
 */
class Customer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Invoicing\Entity\Company")
     * @ORM\JoinColumn(name="company", nullable=false)
     *
     * @var Company
     */
    private $company;

    /**
     * @ORM\Column(name="street_name", type="string", nullable=false)
     *
     * @var string
     */
    private $streetName;

    /**
     * @ORM\Column(name="post_code", type="string", nullable=false, length=10)
     *
     * @var string
     */
    private $postCode;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    private $city;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    private $email;

    /**
     * @param  CustomerModel $model
     * @return Customer
     */
    public static function createFromModel(CustomerModel $model)
    {
        return new Customer(
            $model->getName(),
            $model->getStreetName(),
            $model->getPostCode(),
            $model->getPostCode(),
            $model->getCity(),
            $model->getEmail()
        );
    }

    /**
     * @param string $name
     * @param string $streetName
     * @param string $postCode
     * @param string $city
     * @param string $email
     */
    public function __construct(Company $company, $name, $streetName, $postCode, $city, $email)
    {
        $this->company = $company;
        $this->name = $name;
        $this->streetName = $streetName;
        $this->postCode = $postCode;
        $this->city = $city;
        $this->email = $email;
    }

    public function createOutputModel()
    {
        return new CustomerModel(
            $this->name,
            $this->streetName,
            $this->postCode,
            $this->city,
            $this->email,
            $this->id
        );
    }

    public function updateFromModel(CustomerModel $model)
    {
        $this->name = $model->getName();
        $this->streetName = $model->getStreetName();
        $this->postCode = $model->getPostCode();
        $this->city = $model->getCity();
        $this->email = $model->getEmail();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getStreetName(): string
    {
        return $this->streetName;
    }

    /**
     * @return string
     */
    public function getPostCode(): string
    {
        return $this->postCode;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
