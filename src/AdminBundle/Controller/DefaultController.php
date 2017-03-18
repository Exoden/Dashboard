<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\News;
use AppBundle\Form\NewsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DefaultController extends Controller
{
    /**
     * @Route("/homepage_admin", name="homepage_admin")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('AdminBundle:Default:homepage.html.twig');
    }

    /**
     * @Route("/create_news", name="create_news")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createNewsAction(Request $request)
    {
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
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editNewsAction(Request $request, $news_id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $news = $em->getRepository('AppBundle:News')->find($news_id);

        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->persist($news);
                $em->flush();
                $this->addFlash('success', "Changes saved");
                return $this->redirectToRoute('edit_news', array('news_id' => $news->getId()));
            }
        }

        return $this->render('AdminBundle:Form:edit_news.html.twig', array('news' => $news, 'form' => $form->createView()));
    }

    /**
     * @Route("/list_news", name="list_news")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listNewsAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $news_list = $em->getRepository('AppBundle:News')->getOrderedNews();

        return $this->render('AdminBundle:Default:list_news.html.twig', array('news_list' => $news_list));
    }
}
