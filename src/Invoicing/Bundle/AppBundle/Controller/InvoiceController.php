<?php

namespace Invoicing\Bundle\AppBundle\Controller;

use Invoicing\Exception\InvoiceNotFoundException;
use Invoicing\Model\Invoice\InvoiceModel;
use Invoicing\Service\InvoiceService;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/", service="invoicing.controller.invoice")
 */
class InvoiceController
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
     * @Route("/api/invoices", name="invoice.create", defaults={"_format": "json"})
     * @Method("POST")
     * @param  InvoiceModel $invoice
     * @return Response
     */
    public function createInvoiceAction(InvoiceModel $invoice)
    {
        $this->invoiceService->createInvoice($invoice);
        return new Response(null, 200);
    }

    /**
     * @Route("/api/invoices", name="invoices.get", defaults={"_format": "json"})
     * @Method("GET")
     * @return Response
     */
    public function getInvoicesAction()
    {
        return new Response(
            $this->serializer->serialize($this->invoiceService->getInvoices(), 'json'),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * @Route("/api/invoice/{invoiceId}", name="invoices.edit", defaults={"_format": "json"}, requirements={"invoiceId": "\d+"})
     * @Method("PATCH")
     * @return Response
     */
    public function editInvoiceAction($invoiceId, InvoiceModel $model)
    {
        $this->invoiceService->updateInvoice($invoiceId, $model);

        return new Response(null, 204);
    }

    /**
     * @Route("/api/invoice/{invoiceId}", name="invoices.delete", defaults={"_format": "json"}, requirements={"invoiceId": "\d+"})
     * @Method("DELETE")
     * @return Response
     */
    public function deleteInvoiceAction($invoiceId)
    {
        $this->invoiceService->remove($invoiceId);

        return new Response(null, 200);
    }

    /**
     * @Route("/api/invoice/{invoiceId}", name="invoices.getInvoice", defaults={"_format": "json"}, requirements={"invoiceId": "\d+"})
     * @Method("GET")
     * @throws NotFoundHttpException
     * @return Response
     */
    public function getInvoiceAction($invoiceId)
    {
        try {
            return new Response(
                $this->serializer->serialize($this->invoiceService->getInvoice($invoiceId), 'json'),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (InvoiceNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
