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
}
