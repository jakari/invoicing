<?php

namespace Invoicing\Serializer\Handler;
use Invoicing\Value\Money;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;

class MoneyHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => Money::class,
                'method' => 'serializeMoneyToJson',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => Money::class,
                'method' => 'deserializeMoneyFromJson',
            ]
        ];
    }

    public function serializeMoneyToJson(JsonSerializationVisitor $visitor, $money, array $type, Context $context)
    {
        return $money->getValue() / 100;
    }

    public function deserializeMoneyFromJson(JsonDeserializationVisitor $visitor, $value, array $type, Context $context)
    {
        return new Money(round($value * 100));
    }
}
