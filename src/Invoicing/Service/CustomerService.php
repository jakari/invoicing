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

    public function saveCustomer(CustomerModel $model)
    {
        return $model->shouldCreateNewCustomer()
            ? $this->createCustomer($model)
            : $this->updateCustomer($model);
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

    /**
     * @param  CustomerModel             $model
     * @return Customer
     * @throws CustomerNotFoundException
     */
    public function updateCustomer(CustomerModel $model): Customer
    {
        $customer = $this->repository->get($model->getId());
        $customer->updateFromModel($model);
        $this->repository->update($customer);

        return $customer;
    }

    public function removeCustomer(CustomerModel $customer)
    {
        $this->conn->prepare('DELETE FROM customer WHERE id = :id')
            ->execute([':id' => $customer->getId()]);
    }

    /**
     * @param  string             $name
     * @return CustomerModel|null
     */
    public function search(string $name)
    {
        if ($customer = $this->repository->getFirst($name)) {
            return $customer->createOutputModel();
        }

        return null;
    }
}
