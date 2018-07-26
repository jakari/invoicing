<?php

namespace Invoicing\Value;

class Money
{
    /**
     * @var integer
     */
    private $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function add(Money $money)
    {
        return new Money($this->value + $money->getValue());
    }

    public function multiply($value)
    {
        return new Money(round($this->value * $value));
    }
}
