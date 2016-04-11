<?php

namespace Invoicing\Model;

use JMS\Serializer\Annotation as Serialize;
use Symfony\Component\Validator\Constraints as Assert;

class IdModel {
    /**
     * @Serialize\Type("integer")
     * @var integer|null
     */
    private $id;
}