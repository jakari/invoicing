<?php

namespace Invoicing\Bundle\AppBundle\Request\ParamConverter;

use Invoicing\Bundle\AppBundle\Request\Exception\DeserializingException;
use Invoicing\Bundle\AppBundle\Request\Exception\ValidationException;
use Invoicing\Bundle\AppBundle\Request\Handler\ViolationErrorFormatter;
use JMS\Serializer\Exception\RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class DeserializerParamConverter implements ParamConverterInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ViolationErrorFormatter
     */
    private $violationErrorFormatter;

    /**
     * @param SerializerInterface     $serializer
     * @param ValidatorInterface      $validator
     * @param ViolationErrorFormatter $violationErrorFormatter
     */
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ViolationErrorFormatter $violationErrorFormatter
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->violationErrorFormatter = $violationErrorFormatter;
    }

    /**
     * @param  ParamConverter $configuration
     * @return bool
     */
    public function supports(ParamConverter $configuration)
    {
        return (boolean) $configuration->getClass();
    }

    /**
     * @param  Request                $request
     * @param  ParamConverter         $configuration
     * @return boolean
     * @throws ValidationException
     * @throws DeserializingException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        try {
            $object = $this->deserialize($request, $configuration);
        } catch (RuntimeException $e) {
            throw new DeserializingException($e->getMessage());
        }

        $violations = $this->validator->validate($object);

        if ($violations->count()) {
            throw new ValidationException(
                $violations,
                $this->violationErrorFormatter->violationsToString($violations)
            );
        }

        // set the object as the request attribute with the given name
        // (this will later be an argument for the action)
        $request->attributes->set($configuration->getName(), $object);

        return true;
    }

    /**
     * @param  Request             $request
     * @param  ParamConverter      $configuration
     * @return object|array|scalar
     */
    abstract protected function deserialize(
        Request $request,
        ParamConverter $configuration
    );
}
