<?php

namespace Invoicing\Service;

use Invoicing\Repository\InvoiceRepository;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Yaml\Yaml;

class InvoiceRendererService
{
    /**
     * @var InvoiceRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $settingsPath;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var TwigEngine
     */
    private $twig;

    public function __construct(
        InvoiceRepository $repository,
        string $settingsPath,
        string $rootDir,
        TwigEngine $twig
    ) {
        $this->repository = $repository;
        $this->settingsPath = $settingsPath;
        $this->rootDir = $rootDir;
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

    public function renderPdf($invoiceId): string
    {
        $html = $this->render($invoiceId);
        $input = null;
        $output = null;

        try {
            $input = tempnam(sys_get_temp_dir(), 'invoice_');
            rename($input, $input.'.html');
            $input .= '.html';
            $resource = fopen($input, 'r+');
            fwrite($resource, $html);
            fclose($resource);

            $output = tempnam(sys_get_temp_dir(), 'invoice_');
            $path = $this->rootDir . '/scripts/makepdf.js';

            `node {$path} {$input} {$output}`;

            return file_get_contents($output);
        } finally {
            $input && unlink($input);
            $output && unlink($output);
        }
    }
}
