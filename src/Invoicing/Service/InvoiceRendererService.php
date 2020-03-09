<?php

namespace Invoicing\Service;

use Invoicing\Entity\Invoice\Invoice;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Yaml\Yaml;

class InvoiceRendererService
{
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
        string $settingsPath,
        string $rootDir,
        TwigEngine $twig
    ) {
        $this->settingsPath = $settingsPath;
        $this->rootDir = $rootDir;
        $this->twig = $twig;
    }

    public function render(Invoice $invoice)
    {
        $settings = Yaml::parseFile($this->settingsPath);

        return $this->twig->render(
            "@InvoicingApp/invoice/{$invoice->getTemplate()}.twig",
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

            $result = shell_exec("node {$path} {$input} {$output} 2>&1");

            if ($result !== null) {
                throw new \ErrorException($result);
            }

            return file_get_contents($output);
        } finally {
            $input && unlink($input);
            $output && unlink($output);
        }
    }
}
