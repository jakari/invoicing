<?php

namespace Invoicing\Bundle\AppBundle\Twig;

use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Entity\Invoice\InvoiceItem;
use Invoicing\Value\InvoiceCalculator;
use Invoicing\Value\Money;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class InvoiceExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('itemTotal', array($this, 'itemTotal')),
            new TwigFilter('total', array($this, 'total')),
            new TwigFilter('totalWithoutTax', array($this, 'totalWithoutTax')),
            new TwigFilter('taxTotal', array($this, 'taxTotal')),
            new TwigFilter('money', array($this, 'format')),
        );
    }

    public function itemTotal(InvoiceItem $item)
    {
        return $this->format(InvoiceCalculator::calculateItemTotal($item));
    }

    public function taxTotal(Invoice $invoice)
    {
        return $this->format($invoice->getTaxTotal());
    }

    public function total(Invoice $invoice)
    {
        return $this->format($invoice->getTotal());
    }


    public function totalWithoutTax(Invoice $invoice)
    {
        return $this->format($invoice->getTotalWithoutTax());
    }

    public function format(Money $money): string
    {
        return number_format($money->getValue() / 100, 2, ',', ' ');
    }
}
