<?php

namespace Invoicing\Bundle\AppBundle\Listener;

use Invoicing\Exception\CompanyNotFoundException;
use Invoicing\Exception\CustomerNotFoundException;
use Invoicing\Exception\InvoiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();

        if ($exception instanceof InsufficientAuthenticationException) {
            $response = new Response('', 401);

            $event->setResponse($response);
        }

        if ($exception instanceof InvoiceNotFoundException
            || $exception instanceof CustomerNotFoundException
            || $exception instanceof CompanyNotFoundException
        ) {
            $event->setResponse(new Response('', 404));
        }
    }
}
