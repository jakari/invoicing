<?php

namespace Invoicing\Tests\Integration\Invoicing\Bundle\AppBundle\Controller;

use Invoicing\Tests\IntegrationTestCase;

class InvoicingControllerTest extends IntegrationTestCase
{
    public static function setUpBeforeClass()
    {
        $self = new self();
        $self->resetDatabase();
    }

    public function up()
    {

    }

    /**
     * @test
     */
    public function createsInvoiceAndCustomer()
    {

        $request = $this->aRequest('invoice.create', 'foo');

        var_dump($request->doRequest()->getResponse()->getContent());
    }
}
