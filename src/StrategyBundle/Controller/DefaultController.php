<?php

namespace StrategyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage_strategy")
     */
    public function indexAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', "Access denied");
            return $this->redirectToRoute('homepage');
        }

        return $this->render('StrategyBundle:Default:homepage.html.twig');
    }
    /**
     * @Route("/buildings", name="buildings")
     */
    public function BuildingsAction(Request $request)
    {
        return $this->render('StrategyBundle:Default:buildings.html.twig');
    }
}
