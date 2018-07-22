<?php

namespace Invoicing\Entity\Invoice;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;
use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Model\Invoice\ItemModel;

/**
 * @Entity(repositoryClass="Invoicing\Repository\InvoiceItemRepository")
 * @ORM\Table(name="invoice_item")
 */
class InvoiceItem
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
     * @ORM\ManyToOne(targetEntity="Invoicing\Entity\Invoice\Invoice")
     * @ORM\JoinColumn(name="invoice", referencedColumnName="invoice_number", nullable=false)
     *
     * @var Invoice
     */
    private $invoice;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var string
     */
    private $amount;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var string
     */
    private $tax;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var string
     */
    private $price;

    public static function createFromModel(Invoice $invoice, ItemModel $model)
    {
        return new self(
            $invoice,
            $model->getDescription(),
            $model->getAmount(),
            $model->getTax(),
            $model->getPrice()
        );
    }

    /**
     * @param Invoice $invoice
     * @param string  $description
     * @param integer $amount
     * @param integer $tax
     * @param integer $price
     */
    public function __construct(
        Invoice $invoice,
        $description,
        $amount,
        $tax,
        $price
    ) {
        $this->invoice = $invoice;
        $this->description = $description;
        $this->amount = $amount;
        $this->tax = $tax;
        $this->price = $price;

        $this->invoice->addItem($this);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    public function updateFromModel(ItemModel $model) {
        $this->description = $model->getDescription();
        $this->amount = $model->getAmount();
        $this->price = $model->getPrice();
        $this->tax = $model->getTax();
    }

    public function createOutputModel(): ItemModel {
        return new ItemModel(
            $this->description,
            $this->amount,
            $this->price,
            $this->tax,
            $this->id
        );
    }
}
