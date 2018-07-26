<?php

namespace Invoicing\Service;

use Invoicing\Repository\InvoiceRepository;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Yaml\Yaml;

class InvoiceRendererService
{
    private $repository;

    private $settingsPath;

    private $twig;

    public function __construct(
        InvoiceRepository $repository,
        string $settingsPath,
        TwigEngine $twig
    ) {
        $this->repository = $repository;
        $this->settingsPath = $settingsPath;
        $this->twig = $twig;
    }

    public function render($invoiceId)
    {
        $invoice = $this->repository->get($invoiceId);
        $settings = Yaml::parseFile($this->settingsPath);

        return $this->twig->render(
            '@InvoicingApp/invoice/invoice-template.html.twig',
            [
                'company' => $settings['company'],
                'bank' => $settings['bank'],
                'invoice' => $invoice
            ]
        );
    }
}
