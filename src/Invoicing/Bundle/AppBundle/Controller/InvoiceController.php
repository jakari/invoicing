<?php

namespace Invoicing\Bundle\AppBundle\Controller;

use Invoicing\Exception\InvoiceNotFoundException;
use Invoicing\Model\Invoice\InvoiceModel;
use Invoicing\Repository\InvoiceRepository;
use Invoicing\Service\Sender\InvoiceSenderService;
use Invoicing\Service\InvoiceRendererService;
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
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    /**
     * @var InvoiceSenderService
     */
    private $invoiceSenderService;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var InvoiceRendererService
     */
    private $invoiceRendererService;

    public function __construct(
        InvoiceRepository $invoiceRepository,
        InvoiceSenderService $invoiceSenderService,
        InvoiceService $invoiceService,
        Serializer $serializer,
        InvoiceRendererService $invoiceRendererService
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceSenderService = $invoiceSenderService;
        $this->invoiceService = $invoiceService;
        $this->serializer = $serializer;
        $this->invoiceRendererService = $invoiceRendererService;
    }

    /**
     * @Route("/invoice", name="invoice.create", defaults={"_format": "json"})
     * @Method("POST")
     * @param  InvoiceModel $invoice
     * @return Response
     */
    public function createInvoiceAction(InvoiceModel $invoice)
    {
        return $this->serializeResponse(['invoiceNumber' => $this->invoiceService->createInvoice($invoice)]);
    }

    /**
     * @Route("/invoices", name="invoices.get", defaults={"_format": "json"})
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
     * @Route("/invoice/{invoiceId}", name="invoices.edit", defaults={"_format": "json"}, requirements={"invoiceId": "\d+"})
     * @Method("PATCH")
     * @return Response
     */
    public function editInvoiceAction($invoiceId, InvoiceModel $model)
    {
        $this->invoiceService->updateInvoice($invoiceId, $model);

        return new Response(null, 204);
    }

    /**
     * @Route("/invoice/{invoiceId}", name="invoices.delete", defaults={"_format": "json"}, requirements={"invoiceId": "\d+"})
     * @Method("DELETE")
     * @return Response
     */
    public function deleteInvoiceAction($invoiceId)
    {
        $this->invoiceService->remove($invoiceId);

        return new Response(null, 200);
    }

    /**
     * @Route("/invoice/{invoiceId}", name="invoices.getInvoice", defaults={"_format": "json"}, requirements={"invoiceId": "\d+"})
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

    /**
     * @Route("/invoice/{invoiceId}/print", name="invoices.printInvoice", requirements={"invoiceId": "\d+"})
     * @Method("GET")
     * @throws NotFoundHttpException
     * @return Response
     */
    public function printInvoiceAction($invoiceId)
    {
        try {
            $invoice = $this->invoiceRepository->get($invoiceId);

            return new Response(
                $this->invoiceRendererService->render($invoice),
                200
            );
        } catch (InvoiceNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @Route("/invoice/{invoiceId}/print_html", name="invoices.printInvoiceHtml", requirements={"invoiceId": "\d+"})
     * @Method("GET")
     * @throws NotFoundHttpException
     * @return Response
     */
    public function printInvoiceHtmlAction($invoiceId)
    {
        try {
            $invoice = $this->invoiceRepository->get($invoiceId);

            return new Response(
                $this->invoiceRendererService->render($invoice),
                200
            );
        } catch (InvoiceNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @Route("/invoice/lasku-{invoiceId}.pdf", name="invoices.printInvoicePdf", requirements={"invoiceId": "\d+"})
     * @Method("GET")
     * @throws NotFoundHttpException
     * @return Response
     */
    public function printInvoicePdfAction($invoiceId)
    {
        try {
            $invoice = $this->invoiceRepository->get($invoiceId);

            return new Response(
                $this->invoiceRendererService->renderPdf($invoice),
                200,
                ['Content-Type' => 'application/pdf']
            );
        } catch (InvoiceNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @Route("/invoice/{invoiceId}/send-email", name="invoices.sendInvoiceEmail", requirements={"invoiceId": "\d+"})
     * @Method("POST")
     * @throws NotFoundHttpException
     * @return Response
     */
    public function sendInvoiceEmail($invoiceId)
    {
        try {
            $invoice = $this->invoiceRepository->get($invoiceId);

            $this->invoiceSenderService->send($invoice);

            return new Response('',200);
        } catch (InvoiceNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @Route("/invoice/settings", name="invoices.settings", defaults={"_format": "json"})
     * @Method("GET")
     * @return Response
     */
    public function getSettingsAction()
    {
        return $this->serializeResponse(
            $this->invoiceService->getSettings()
        );
    }

    private function serializeResponse($data)
    {
        return new Response(
            $this->serializer->serialize($data, 'json'),
            200,
            ['Content-Type' => 'application/json']
        );
    }
}
