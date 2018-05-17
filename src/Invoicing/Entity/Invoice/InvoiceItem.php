<?php

namespace Invoicing\Entity\Invoice;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;
use Invoicing\Entity\Invoice;

/**
 * @Entity(repositoryClass="Invoicing\Repository\InvoiceItemRepository")
 * @ORM\Table(name="invoice_item")
 */
class InvoiceItem
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
     * @ORM\ManyToOne(targetEntity="Invoicing\Entity\Invoice")
     * @ORM\JoinColumn(name="invoice", referencedColumnName="id", nullable=false)
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
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }
}
