<?php

namespace Invoicing\Bundle\AppBundle\Controller;

use Invoicing\Model\Invoice\CustomerModel;
use Invoicing\Service\CustomerService;

use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/", service="invoicing.controller.customer")
 */
class CustomerController
{
    /**
     * @var CustomerService
     */
    private $service;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(CustomerService $service, Serializer $serializer)
    {
        $this->service = $service;
        $this->serializer = $serializer;
    }


    /**
     * @Route("/customers", name="customer.create", defaults={"_format": "json"})
     * @Method("POST")
     * @param CustomerModel $model
     */
    public function createCustomerAction(CustomerModel $customer)
    {

    }

    /**
     * @Route("/customers/{name}", name="customer.search", defaults={"_format": "json"})
     * @Method("GET")
     * @param string
     * @return Response
     */
    public function searchCustomersAction(string $name)
    {
        if ($customer = $this->service->search($name)) {
            return new Response(
                $this->serializer->serialize($customer, 'json'),
                200,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response('', 404);
    }
}
