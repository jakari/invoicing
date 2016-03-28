<?php

namespace Invoicing\Bundle\AppBundle\Request\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends \Exception
{
    /**
     * @var ConstraintViolationListInterface
     */
    private $violations;

    /**
     * @param ConstraintViolationListInterface $violations The list of validation errors.
     * @param string                           $error      The errors represented as a string
     */
    public function __construct(ConstraintViolationListInterface $violations, $error)
    {
        $this->violations = $violations;

        parent::__construct($error);
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolationList()
    {
        return $this->violations;
    }
}
