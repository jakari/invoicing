<?php

namespace Invoicing\Entity\Customer;

use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;
use Invoicing\Entity\Company;
use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Model\Invoice\CustomerModel;
use Invoicing\Model\Customer\FullCustomerModel;

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
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $vat;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity="Invoicing\Entity\Invoice\Invoice", mappedBy="customer")
     *
     * @var DoctrineCollection
     */
    private $invoices;

    public function __construct(
        Company $company,
        string $name,
        string $streetName,
        string $postCode,
        string $city,
        string $email,
        string $vat = null,
        string $phone = null
    ) {
        $this->company = $company;
        $this->name = $name;
        $this->streetName = $streetName;
        $this->postCode = $postCode;
        $this->city = $city;
        $this->email = $email;
        $this->vat = $vat;
        $this->phone = $phone;
    }

    public function createOutputModel()
    {
        return new CustomerModel(
            $this->name,
            $this->streetName,
            $this->postCode,
            $this->city,
            $this->email,
            $this->id,
            $this->vat,
            $this->phone
        );
    }

    public function createFullOutputModel()
    {
        return new FullCustomerModel(
            $this->name,
            $this->streetName,
            $this->postCode,
            $this->city,
            $this->email,
            $this->id,
            $this->vat,
            $this->phone,
            $this->invoices
                ->map(function (Invoice $invoice) {
                    return $invoice->createListItemModel();
                })
                ->toArray()
        );
    }

    public function updateFromModel(CustomerModel $model)
    {
        $this->name = $model->getName();
        $this->streetName = $model->getStreetName();
        $this->postCode = $model->getPostCode();
        $this->city = $model->getCity();
        $this->email = $model->getEmail();
        $this->vat = $model->getVat();
        $this->phone = $model->getPhone();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Company
     */
    public function getCompany(): Company
    {
        return $this->company;
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

    public function getVat(): ?string
    {
        return $this->vat;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }
}
