<?php

namespace Invoicing\Bundle\AppBundle\Controller;

use Invoicing\Service\InvoiceService;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/", service="invoicing.controller.security")
 */
class SecurityController extends Controller
{
    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        InvoiceService $invoiceService,
        Serializer $serializer
    ) {
        $this->invoiceService = $invoiceService;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
        return new Response(
            $this->serializer->serialize(
                $this->invoiceService->getSettings(),
                'json'
            ),
            200,
            ['Content-Type' => 'application/json']
        );
    }
}