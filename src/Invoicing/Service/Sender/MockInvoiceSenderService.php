<?php

namespace Invoicing\Service\Sender;

use Invoicing\Entity\Invoice\Invoice;

class MockInvoiceSenderService implements InvoiceSenderService
{
    public function send(Invoice $invoice)
    {
    }
}
