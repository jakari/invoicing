<?php

namespace Invoicing\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class InvoiceStatusType extends Type
{
    const STATUS_NAME = 'invoice_status';

    /**
     * @param  array            $fieldDeclaration
     * @param  AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return self::STATUS_NAME;
    }

    /**
     * @param  mixed            $value
     * @param  AbstractPlatform $platform
     * @return mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    /**
     * @param  mixed $value
     * @param  AbstractPlatform $platform
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::STATUS_NAME;
    }
}
