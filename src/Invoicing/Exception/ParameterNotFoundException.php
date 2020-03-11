<?php

namespace Invoicing\Exception;

use Throwable;

class ParameterNotFoundException extends \Exception
{
    public function __construct(string $key)
    {
        parent::__construct("Parameter ${key} not found.");
    }
}
