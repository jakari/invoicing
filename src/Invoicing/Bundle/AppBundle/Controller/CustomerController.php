<?php

namespace Invoicing\Bundle\AppBundle\Controller;

use Invoicing\Model\Invoice\CustomerModel;
use Invoicing\Service\CustomerService;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/", service="invoicing.controller.invoice")
 */
class CustomerController
{
    /**
     * @var CustomerService
     */
    private $service;

    public function __construct(CustomerService $service)
    {
        $this->service = $service;
    }


    /**
     * @Route("/api/customers", name="customer.create", defaults={"_format": "json"})
     * @Method("POST")
     * @param CustomerModel $model
     */
    public function createCustomerAction(CustomerModel $customer)
    {

    }
}
