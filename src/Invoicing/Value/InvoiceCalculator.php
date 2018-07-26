<?php

namespace Invoicing\Value;

use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Entity\Invoice\InvoiceItem;

class InvoiceCalculator
{
    public static function calculateItemTotal(InvoiceItem $item): Money
    {
        return self::calculateItemTaxAmount($item)->add(
            self::calculateItemTotalWithoutTax($item)
        );
    }

    public static function calculateItemTaxAmount(InvoiceItem $item): Money
    {
        return self::calculateItemTotalWithoutTax($item)->multiply($item->getTax() * 0.01);
    }

    public static function calculateItemTotalWithoutTax(InvoiceItem $item): Money
    {
        return $item->getPrice()->multiply($item->getAmount());
    }
}
