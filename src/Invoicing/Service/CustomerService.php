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

    /**
     * @var CurrentCompanyService
     */
    private $currentCompanyService;

    public function __construct(
        CustomerRepository $repository,
        Connection $connection,
        CurrentCompanyService $currentCompanyService
    ) {
        $this->repository = $repository;
        $this->conn = $connection;
        $this->currentCompanyService = $currentCompanyService;
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
            $this->currentCompanyService->get(),
            $model->getName(),
            $model->getStreetName(),
            $model->getPostCode(),
            $model->getCity(),
            $model->getEmail(),
            $model->getVat()
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

    public function getAll()
    {
        return array_map(
            function (Customer $customer) {
                return $customer->createOutputModel();
            },
            $this->repository->getAll($this->currentCompanyService->get())
        );
    }

    public function get(int $customerId)
    {
        return $this->repository->get($customerId)->createFullOutputModel();
    }

    /**
     * @param  string          $name
     * @return CustomerModel[]
     */
    public function search(string $name)
    {
        $company = $this->currentCompanyService->getId();
        return array_map(
            function (Customer $customer) {
                return $customer->createOutputModel();
            },
            $this->repository->findByName($company, $name)
        );
    }
}
