<?php

namespace Invoicing\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Entity(repositoryClass="Invoicing\Repository\ParameterRepository")
 * @ORM\Table(name="parameters")
 */
class Parameters
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Invoicing\Entity\Company")
     * @ORM\JoinColumn(name="company", nullable=false)
     *
     * @var Company
     */
    private $company;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    private $key;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    private $value;
}
