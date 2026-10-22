<?php

namespace Invoicing\Service;

use Invoicing\Exception\ParameterNotFoundException;
use Invoicing\Repository\ParameterRepository;
use Symfony\Component\Yaml\Yaml;

class ParameterService
{
    private $currentCompanyService;

    private $repository;

    /**
     * @var string
     */
    private $defaultSettings;

    public function __construct(
        CurrentCompanyService $currentCompanyService,
        ParameterRepository $repository,
        string $defaultSettings
    ) {
        $this->currentCompanyService = $currentCompanyService;
        $this->repository = $repository;
        $this->defaultSettings = $defaultSettings;
    }

    public function getInitialInvoiceNumber()
    {
        return $this->get('initial_invoice_number');
    }

    public function getInitialReferenceNumber()
    {
        return $this->get('initial_reference_number');
    }

    /**
     * The company's parameter, or the value under "default" in the settings file when the company has none.
     *
     * @throws ParameterNotFoundException
     */
    private function get(string $key): string
    {
        try {
            return $this->repository->get($this->currentCompanyService->get(), $key);
        } catch (ParameterNotFoundException $e) {
            $settings = Yaml::parseFile($this->defaultSettings);

            if (!isset($settings['default'][$key])) {
                throw $e;
            }

            return (string) $settings['default'][$key];
        }
    }
}
