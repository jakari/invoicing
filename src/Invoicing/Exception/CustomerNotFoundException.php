<?php

namespace Invoicing\Exception;

class CustomerNotFoundException extends \ErrorException
{
    public function __construct(int $id)
    {
        parent::__construct("Customer with \"{$id}\" not found.");
    }
}
