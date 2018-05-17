<?php

namespace Invoicing\Entity\Customer;

use Doctrine\Common\Collections\ArrayCollection as DoctrineCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;
use Invoicing\Model\Invoice\CustomerModel;

/**
 * @Entity(repositoryClass="Invoicing\Repository\CustomerRepository")
 */
class Customer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
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
    public function __construct($name, $streetName, $postCode, $city, $email)
    {
        $this->name = $name;
        $this->streetName = $streetName;
        $this->postCode = $postCode;
        $this->city = $city;
        $this->email = $email;
    }
}
