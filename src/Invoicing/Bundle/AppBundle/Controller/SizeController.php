<?php

namespace Invoicing\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SizeController extends Controller
{
    /**
     * @Route("/", name="site_indez")
     * @Template("@InvoicingApp/index.html.twig")
     *
     * @return array
     */
    public function indexAction()
    {
        return [];
    }
}