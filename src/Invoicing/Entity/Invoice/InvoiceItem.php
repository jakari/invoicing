<?php

namespace Invoicing\Entity\Invoice;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;
use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Model\Invoice\ItemModel;
use Invoicing\Value\Money;

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
     * @var integer
     */
    private $amount;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var integer
     */
    private $tax;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var integer
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

    public function __construct(
        Invoice $invoice,
        string $description,
        int $amount,
        int $tax,
        Money $price
    ) {
        $this->invoice = $invoice;
        $this->description = $description;
        $this->amount = $amount;
        $this->tax = $tax;
        $this->price = $price->getValue();

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
        $this->price = $model->getPrice()->getValue();
        $this->tax = $model->getTax();
    }

    public function createOutputModel(): ItemModel {
        return new ItemModel(
            $this->description,
            $this->amount,
            $this->getPrice(),
            $this->tax,
            $this->id
        );
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return integer
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @return integer
     */
    public function getTax(): string
    {
        return $this->tax;
    }

    /**
     * @return Money
     */
    public function getPrice(): Money
    {
        return new Money($this->price);
    }
}
