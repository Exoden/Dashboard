<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\News;
use AppBundle\Form\NewsType;
use IdleBundle\Entity\Item;
use IdleBundle\Entity\Stuff;
use IdleBundle\Entity\Utils;
use IdleBundle\Form\StuffType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage_admin")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        return $this->render('AdminBundle:Default:homepage.html.twig');
    }

    /**
     * @Route("/create-news", name="create_news")
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
     * @Route("/edit-news/{news_id}", name="edit_news")
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
     * @Route("/list-news", name="list_news")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function listNewsAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $news_list = $em->getRepository('AppBundle:News')->getOrderedNews();

        return $this->render('AdminBundle:Default:list_news.html.twig', array('news_list' => $news_list));
    }

    /**
     * @Route("/idle-generator", name="idle_generator")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function idleGeneratorAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $items = $em->getRepository('IdleBundle:Item')->findAll();

        return $this->render('AdminBundle:Default:idle_generator.html.twig', array('items' => $items));
    }

    /**
     * @Route("/create-stuff", name="create_stuff")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createStuffAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $stuff = new Stuff();
        $form = $this->createForm(StuffType::class, $stuff);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $stuff->getItem()->setTypeItem($em->getRepository('IdleBundle:TypeItem')->findOneBy(array('name' => "Equipment")));

                $item = $form['item']->getData();
                $filename = $this->get('app.file_uploader')->upload(Utils::slugify($item->getName()), 'Equipments', $item->getImage());
                $item->setImage($filename);

                $em->persist($stuff);
                $em->flush();

                $this->addFlash('success', "New stuff saved");

                return $this->redirectToRoute('edit_stuff', array('item_id' => $stuff->getItem()->getId()));
            }
        }

        return $this->render('AdminBundle:Form:create_stuff.html.twig', array('form' => $form->createView()));
    }


    /**
     * @Route("/edit-stuff/{item_id}", name="edit_stuff")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editStuffAction(Request $request, $item_id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        /** @var Stuff $stuff */
        $stuff = $em->getRepository('IdleBundle:Stuff')->findOneBy(array('item' => $item_id));

        $saved_image = $stuff->getItem()->getImage();
        if ($saved_image != null)
            $stuff->getItem()->setImage(new File($this->getParameter('idle_images_directory') . '/Equipments/' . $saved_image));

        $form = $this->createForm(StuffType::class, $stuff);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $stuff->getItem()->setTypeItem($em->getRepository('IdleBundle:TypeItem')->findOneBy(array('name' => "Equipment")));

                $filename = $saved_image;
                $item = $form['item']->getData();
                if ($item->getImage()) {
                    $filename = $this->get('app.file_uploader')->upload(Utils::slugify($item->getName()), 'Equipments', $item->getImage());
                }
                $item->setImage($filename);

                $em->persist($stuff);
                $em->flush();

                $this->addFlash('success', "Changes saved");

                return $this->redirectToRoute('edit_stuff', array('item_id' => $stuff->getItem()->getId()));
            }
        }

        return $this->render('AdminBundle:Form:edit_stuff.html.twig', array('form' => $form->createView()));
    }


    /**
     * @Route("/remove-stuff/{item_id}", name="remove_stuff")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeStuffAction(Request $request, $item_id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $stuff = $em->getRepository('IdleBundle:Stuff')->findOneBy(array('item' => $item_id));

        if ($stuff->getItem()->getImage() != null) {
            if (file_exists($this->get('app.file_uploader')->getTargetDir() . '/Equipments/' . $stuff->getItem()->getImage()))
                unlink($this->get('app.file_uploader')->getTargetDir() . '/Equipments/' . $stuff->getItem()->getImage());
        }

        $em->remove($stuff);
        $em->flush();

        $this->addFlash('success', "Item removed");

        return $this->redirectToRoute('idle_generator');
    }
}
