<?php

namespace Invoicing\Service\Sender;

use Invoicing\Entity\Invoice\Invoice;

interface InvoiceSenderService
{
    public function send(Invoice $invoice);
}
