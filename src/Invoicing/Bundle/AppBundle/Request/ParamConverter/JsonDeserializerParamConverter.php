<?php

namespace Soilscout\Bundle\MainBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class JsonDeserializerParamConverter extends DeserializerParamConverter
{
    /**
     * @param  Request             $request
     * @param  ParamConverter      $configuration
     * @return object|array|scalar
     */
    protected function deserialize(Request $request, ParamConverter $configuration)
    {
        return $this->serializer->deserialize(
            $request->getContent(),
            $configuration->getClass(),
            'json'
        );
    }
}
