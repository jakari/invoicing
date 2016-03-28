<?php

namespace Invoicing\Service;

use Invoicing\Model\Invoice\InvoiceModel;

class InvoiceService
{
    /**
     * @var Connection
     */
    private $pdoService;

    /**
     * @param Connection $pdoService
     */
    public function __construct(Connection $pdoService)
    {
        $this->pdoService = $pdoService;
    }

    public function createInvoice(InvoiceModel $model)
    {
    }
}
