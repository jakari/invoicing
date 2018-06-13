<?php

namespace Invoicing\Service;

use Invoicing\Database\Connection\Connection;
use Invoicing\Entity\Customer\Customer;
use Invoicing\Model\Invoice\CustomerModel;
use Invoicing\Repository\CustomerRepository;

class CustomerService
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var CustomerRepository
     */
    private $repository;

    public function __construct(CustomerRepository $repository, Connection $connection)
    {
        $this->repository = $repository;
        $this->conn = $connection;
    }

    public function createCustomer(CustomerModel $model)
    {
        $customer = new Customer(
            $model->getName(),
            $model->getStreetName(),
            $model->getPostCode(),
            $model->getCity(),
            $model->getEmail()
        );
        $this->repository->save($customer);
        return $customer;
    }

    public function updateCustomer(CustomerModel $customer)
    {
        $this->conn->prepare('UPDATE customer SET
                name = :name,
                street_name = :street_name,
                post_code = :post_code,
                city = :city,
                email = :email
            WHERE id = :id')
            ->execute([
            ':name' => $customer->getName(),
            ':street_name' => $customer->getStreetName(),
            ':post_code' => $customer->getPostCode(),
            ':city' => $customer->getCity(),
            ':email' => $customer->getEmail(),
            'id' => $customer->getId(),
        ]);
    }

    public function removeCustomer(CustomerModel $customer)
    {
        $this->conn->prepare('DELETE FROM customer WHERE id = :id')
            ->execute([':id' => $customer->getId()]);
    }
}
