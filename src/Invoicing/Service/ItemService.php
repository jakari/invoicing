<?php

namespace Invoicing\Service;

use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Entity\Invoice\InvoiceItem;
use Invoicing\Model\Invoice\ItemModel;
use Invoicing\Repository\InvoiceItemRepository;

class ItemService
{
    /**
     * @var InvoiceItemRepository
     */
    private $repository;

    public function __construct(InvoiceItemRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Invoice   $invoice
     * @param ItemModel $item
     */
    public function addItem(Invoice $invoice, ItemModel $item)
    {
        $item = new InvoiceItem(
            $invoice,
            $item->getDescription(),
            $item->getAmount(),
            $item->getTax(),
            $item->getPrice()
        );

        $this->repository->add($item);
    }

    public function updateItem(ItemModel $model)
    {
        $item = $this->repository->get($model->getId());
        $item->updateFromModel($model);
    }

    public function flush() {
        $this->repository->flush();
    }

    /**
     * @param  Invoice     $invoice
     * @return ItemModel[]
     */
    public function getItems($invoice)
    {
        return array_map(
            function (InvoiceItem $item) {
                return $item->createOutputModel();
            },
            $this->repository->findBy(['invoice' => $invoice])
        );
    }

    public function removeItem(ItemModel $item)
    {
        $this->repository->remove($this->repository->get($item->getId()));
    }
}
