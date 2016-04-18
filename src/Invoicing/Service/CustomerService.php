<?php

namespace Invoicing\Service;

use Invoicing\Database\Connection\Connection;
use Invoicing\Model\Invoice\CustomerModel;

class CustomerService
{
    /**
     * @var Connection
     */
    private $conn;

    public function __construct(Connection $connection)
    {
        $this->conn = $connection;
    }

    public function createCustomer(CustomerModel $customer)
    {
        $stmt = $this->conn
            ->prepare('INSERT INTO customer(name, street_name, post_code, city, email) VALUES(:name, :street_name, :post_code, :city, :email)');

        $stmt->execute([
            ':name' => $customer->getName(),
            ':street_name' => $customer->getStreetName(),
            ':post_code' => $customer->getPostCode(),
            ':city' => $customer->getCity(),
            ':email' => $customer->getEmail(),
        ]);

        $customer->setId((int) $this->conn->lastInsertId('customer_id_seq'));
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
