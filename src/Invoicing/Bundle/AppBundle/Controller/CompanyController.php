<?php

namespace Invoicing\Bundle\AppBundle\Controller;

use Invoicing\Service\CurrentCompanyService;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/", service="invoicing.controller.company")
 */
class CompanyController
{
    private $currentCompanyService;

    public function __construct(CurrentCompanyService $currentCompanyService)
    {
        $this->currentCompanyService = $currentCompanyService;
    }


    /**
     * @Route("/select-company/{company}", name="company.select_current", requirements={"invoiceId": "\d+"}, defaults={"_format": "json"})
     * @Method("POST")
     * @return Response
     */
    public function selectCurrentCompany(int $company)
    {
        $this->currentCompanyService->set($company);

        return new Response('', 204);
    }
}
