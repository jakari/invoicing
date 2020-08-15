<?php

namespace Invoicing\Model\Customer;

use Invoicing\Model\Invoice\InvoiceListItemModel;
use JMS\Serializer\Annotation as Serialize;
use Invoicing\Model\Invoice\CustomerModel;

class FullCustomerModel extends CustomerModel
{
    /**
     * @Serialize\Type("array<Invoicing\Model\Invoice\InvoiceListItemModel>")
     *
     * @var InvoiceListItemModel[]
     */
    private $invoices;

    /**
     * @param string       $name
     * @param string       $streetName
     * @param string       $postCode
     * @param string       $city
     * @param string       $email
     * @param integer|null $id
     * @param string|null  $vat
     * @param string|null  $phone
     * @param InvoiceListItemModel[] $invoices
     */
    public function __construct(
        $name,
        $streetName,
        $postCode,
        $city,
        $email,
        $id = null,
        $vat = null,
        $phone = null,
        array $invoices = []
    ) {
        parent::__construct(
            $name,
            $streetName,
            $postCode,
            $city,
            $email,
            $id,
            $vat,
            $phone
        );

        $this->invoices = $invoices;
    }
}
