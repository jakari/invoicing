<?php

namespace Invoicing\Bundle\AppBundle\Controller;

use Invoicing\Model\Invoice\InvoiceModel;
use Invoicing\Service\InvoiceService;

class InvoiceController
{
    /**
     * @var InvoiceService
     */
    private $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function createInvoiceAction(InvoiceModel $model)
    {

    }
}
