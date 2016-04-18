<?php

namespace Invoicing\Service;

use Invoicing\Database\Connection\Connection;
use Invoicing\Model\Invoice\ItemModel;

class ItemService
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param integer   $invoiceId
     * @param ItemModel $item
     */
    public function addItem($invoiceId, ItemModel $item)
    {
        $stmt = $this->connection
            ->prepare('INSERT INTO
                item(invoice, description, amount, tax, price)
                VALUES(:invoice, :description, :amount, :tax, :price)'
            );

        $stmt->execute([
            ':invoice' => $invoiceId,
            ':description' => $item->getDescription(),
            ':amount' => $item->getAmount(),
            ':tax' => $item->getTax(),
            ':price' => $item->getPrice(),
        ]);
    }

    public function updateItem(ItemModel $item)
    {
        $this->connection->prepare('
            UPDATE item SET
                description = :description,
                amount = :amount,
                tax = :tax,
                price = :price
            WHERE id = :id')
        ->execute([
            ':description' => $item->getDescription(),
            ':amount' => $item->getAmount(),
            ':tax' => $item->getTax(),
            ':price' => $item->getPrice(),
            ':id' => $item->getId(),
        ]);
    }

    /**
     * @param  integer     $invoiceId
     * @return ItemModel[]
     */
    public function getItems($invoiceId)
    {
        $stmt = $this->connection->prepare('SELECT * FROM item WHERE invoice = :invoice');
        $stmt->execute([':invoice' => $invoiceId]);

        return array_map(
            function (array $item) {
                return new ItemModel(
                    $item['description'],
                    $item['amount'],
                    $item['price'],
                    $item['tax'],
                    $item['id']
                );
            },
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    public function removeItem(ItemModel $item)
    {
        $this->connection->prepare('DELETE FROM item WHERE id = :id')
            ->execute([':id' => $item->getId()]);
    }
}
