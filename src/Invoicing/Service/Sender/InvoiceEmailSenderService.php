<?php

namespace Invoicing\Service\Sender;

use Invoicing\Entity\Invoice\Invoice;
use Invoicing\Service\InvoiceRendererService;
use Invoicing\Service\InvoiceService;
use Symfony\Component\Yaml\Yaml;

class InvoiceEmailSenderService implements InvoiceSenderService
{
    /**
     * @var InvoiceService
     */
    private $service;

    /**
     * @var Invoice
     */
    private $invoiceRendererService;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var array
     */
    private $settings;

    public function __construct(
        InvoiceService $invoiceService,
        InvoiceRendererService $invoiceRendererService,
        \Swift_Mailer $mailer,
        string $settings
    ) {
        $this->service = $invoiceService;
        $this->invoiceRendererService = $invoiceRendererService;
        $this->mailer = $mailer;
        $this->settings = Yaml::parseFile($settings);
    }

    public function send(Invoice $invoice)
    {
        $pdf = $this->invoiceRendererService->renderPdf($invoice);

        $message = new \Swift_Message();
        $message->attach(
            new \Swift_Attachment(
                $pdf,
                'lasku-'.$invoice->getInvoiceNumber().'.pdf',
                'application/pdf'
            )
        );
        $message->addFrom(
            $this->settings['email']['from']['address'],
            $this->settings['email']['from']['name']
        );
        $message->addTo($invoice->getCustomer()->getEmail());
        $message->setSubject($this->settings['email']['subject']);
        $message->setBody($this->settings['email']['body']);
        $this->mailer->send($message);
    }
}
