<?php

namespace Invoicing\Service\Sender;

use Invoicing\Service\InvoiceRendererService;
use Invoicing\Service\InvoiceService;

class InvoiceSenderFactory
{
    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var InvoiceRendererService
     */
    private $invoiceRendererService;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $settings;

    public function __construct(
        InvoiceService $invoiceService,
        InvoiceRendererService $invoiceRendererService,
        \Swift_Mailer $mailer,
        string $settings
    ) {
        $this->invoiceService = $invoiceService;
        $this->invoiceRendererService = $invoiceRendererService;
        $this->mailer = $mailer;
        $this->settings = $settings;
    }

    public function create(bool $useMock = false): InvoiceSenderService {
        return $useMock ?
            new MockInvoiceSenderService() :
            new InvoiceEmailSenderService(
                $this->invoiceService,
                $this->invoiceRendererService,
                $this->mailer,
                $this->settings
            );
    }
}
