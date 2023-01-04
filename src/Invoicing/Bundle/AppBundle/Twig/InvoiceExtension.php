<?php

namespace Invoicing\Bundle\AppBundle\Twig;

use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Entity\Invoice\InvoiceItem;
use Invoicing\Value\InvoiceCalculator;
use Invoicing\Value\Money;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class InvoiceExtension extends AbstractExtension
{
    private $fileLocator;

    public function __construct(FileLocator $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('itemTotal', array($this, 'itemTotal')),
            new TwigFilter('itemTotalWithoutTax', array($this, 'itemTotalWithoutTax')),
            new TwigFilter('total', array($this, 'total')),
            new TwigFilter('totalWithoutTax', array($this, 'totalWithoutTax')),
            new TwigFilter('taxTotal', array($this, 'taxTotal')),
            new TwigFilter('money', array($this, 'format')),
            new TwigFilter('base64image', array($this, 'base64image')),
        );
    }

    public function itemTotal(InvoiceItem $item)
    {
        return $this->format(InvoiceCalculator::calculateItemTotal($item));
    }

    public function itemTotalWithoutTax(InvoiceItem $item)
    {
        return $this->format(InvoiceCalculator::calculateItemTotalWithoutTax($item));
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

    public function base64image(string $src)
    {
        $path = $this->fileLocator->locate($src);
        $mime = mime_content_type($path);
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}
