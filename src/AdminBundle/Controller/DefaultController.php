<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\News;
use AppBundle\Form\NewsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    /**
     * @Route("/homepage_admin", name="homepage_admin")
     */
    public function indexAction()
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Access denied");
        }

        return $this->render('AdminBundle:Default:homepage.html.twig');
    }

    /**
     * @Route("/create_news", name="create_news")
     */
    public function createNewsAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Access denied");
        }

        $em = $this->getDoctrine()->getEntityManager();

        $news = new News();
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->persist($news);
                $em->flush();
                $this->addFlash('success', "New news saved");
                return $this->redirectToRoute('edit_news', array('news_id' => $news->getId()));
            }
        }

        return $this->render('AdminBundle:Form:create_news.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/edit_news/{news_id}", name="edit_news")
     */
    public function editNewsAction(Request $request, $news_id)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Access denied");
        }

        $em = $this->getDoctrine()->getEntityManager();

        $news = $em->getRepository('AppBundle:News')->find($news_id);

        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->persist($news);
                $em->flush();
                $this->addFlash('success', "New news saved");
                return $this->redirectToRoute('edit_news', array('news_id' => $news->getId()));
            }
        }

        return $this->render('AdminBundle:Form:edit_news.html.twig', array('news' => $news, 'form' => $form->createView()));
    }
}
