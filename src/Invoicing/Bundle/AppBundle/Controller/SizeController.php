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
     * @Route("/login", name="login")
     * @Template("@InvoicingApp/login.html.twig")
     *
     * @return array
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        return ['error' => $authenticationUtils->getLastAuthenticationError()];
    }

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