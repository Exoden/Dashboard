<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\News;
use AppBundle\Form\NewsType;
use Doctrine\Common\Collections\ArrayCollection;
use IdleBundle\Entity\Craft;
use IdleBundle\Entity\Item;
use IdleBundle\Entity\Recipe;
use IdleBundle\Entity\Resource;
use IdleBundle\Entity\Stuff;
use IdleBundle\Entity\Utils;
use IdleBundle\Form\RecipeType;
use IdleBundle\Form\ResourceType;
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

        $items = $em->getRepository('IdleBundle:Item')->getAllSortedItems();

        return $this->render('AdminBundle:Default:idle_generator.html.twig', array('items' => $items));
    }

    /**
     * @Route("/edit-item/{item_id}", name="edit_item")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editItemAction(Request $request, $item_id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        /** @var Item $item */
        $item = $em->getRepository('IdleBundle:Item')->find($item_id);

        if ($item->getTypeItem()->getName() == "Equipment")
            return $this->redirectToRoute('edit_stuff', array('item_id' => $item->getId()));
        else if ($item->getTypeItem()->getName() == "Resource")
            return $this->redirectToRoute('edit_resource', array('item_id' => $item->getId()));
        else if ($item->getTypeItem()->getName() == "Recipe")
            return $this->redirectToRoute('edit_recipe', array('item_id' => $item->getId()));

        return $this->render('AdminBundle:Form:idle_generator.html.twig');
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

    /**
     * @Route("/create-resource", name="create_resource")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createResourceAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $resource = new Resource();
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $resource->getItem()->setTypeItem($em->getRepository('IdleBundle:TypeItem')->findOneBy(array('name' => "Resource")));

                $item = $form['item']->getData();
                $filename = $this->get('app.file_uploader')->upload(Utils::slugify($item->getName()), 'Resources', $item->getImage());
                $item->setImage($filename);

                $em->persist($resource);
                $em->flush();

                $this->addFlash('success', "New resource saved");

                return $this->redirectToRoute('edit_resource', array('item_id' => $resource->getItem()->getId()));
            }
        }

        return $this->render('AdminBundle:Form:create_resource.html.twig', array('form' => $form->createView()));
    }
    /**
     * @Route("/edit-resource/{item_id}", name="edit_resource")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editResourceAction(Request $request, $item_id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $resource = $em->getRepository('IdleBundle:Resource')->findOneBy(array('item' => $item_id));

        $saved_image = $resource->getItem()->getImage();
        if ($saved_image != null)
            $resource->getItem()->setImage(new File($this->getParameter('idle_images_directory') . '/Resources/' . $saved_image));
        
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $resource->getItem()->setTypeItem($em->getRepository('IdleBundle:TypeItem')->findOneBy(array('name' => "Equipment")));

                $filename = $saved_image;
                $item = $form['item']->getData();
                if ($item->getImage()) {
                    $filename = $this->get('app.file_uploader')->upload(Utils::slugify($item->getName()), 'Equipments', $item->getImage());
                }
                $item->setImage($filename);

                $em->persist($resource);
                $em->flush();

                $this->addFlash('success', "Changes saved");

                return $this->redirectToRoute('edit_resource', array('item_id' => $resource->getItem()->getId()));
            }
        }

        return $this->render('AdminBundle:Form:edit_resource.html.twig', array('form' => $form->createView()));
    }
    /**
     * @Route("/remove-resource/{item_id}", name="remove_resource")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeResourceAction(Request $request, $item_id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $resource = $em->getRepository('IdleBundle:Resource')->findOneBy(array('item' => $item_id));

        if ($resource->getItem()->getImage() != null) {
            if (file_exists($this->get('app.file_uploader')->getTargetDir() . '/Resources/' . $resource->getItem()->getImage()))
                unlink($this->get('app.file_uploader')->getTargetDir() . '/Resources/' . $resource->getItem()->getImage());
        }

        $em->remove($resource);
        $em->flush();

        $this->addFlash('success', "Item removed");

        return $this->redirectToRoute('idle_generator');
    }

    /**
     * @Route("/create-recipe", name="create_recipe")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function createRecipeAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $item = new Item();
                $item->setName($form['itemCreated']->getData()->getName());
                $item->setImage('recipe.png');
                $item->setTypeItem($em->getRepository('IdleBundle:TypeItem')->findOneBy(array('name' => "Recipe")));
                $recipe->setItem($item);

                $em->persist($recipe);
                $em->flush();

                $this->addFlash('success', "New resource saved");

                return $this->redirectToRoute('edit_recipe', array('item_id' => $recipe->getItem()->getId()));
            }
        }

        return $this->render('AdminBundle:Form:create_recipe.html.twig', array('form' => $form->createView()));
    }
    /**
     * @Route("/make-recipe-item/{item_id}", name="make_recipe_item")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function makeRecipeItemAction(Request $request, $item_id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $recipe = new Recipe();
        $item = $em->getRepository('IdleBundle:Item')->find($item_id);
        $old_recipe = $em->getRepository('IdleBundle:Recipe')->findOneBy(array('itemCreated' => $item));
        if ($old_recipe) {
            $this->addFlash('warning', 'Already has a recipe, redirect to edit');
            return $this->redirectToRoute('edit_recipe', array('item_id' => $old_recipe->getItem()->getId()));
        }
        $recipe->setItemCreated($item);

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $item = new Item();
                $item->setName($form['itemCreated']->getData()->getName());
                $item->setImage('recipe.png');
                $item->setTypeItem($em->getRepository('IdleBundle:TypeItem')->findOneBy(array('name' => "Recipe")));
                $recipe->setItem($item);

                $em->persist($recipe);
                $em->flush();

                $this->addFlash('success', "New resource saved");

                return $this->redirectToRoute('edit_recipe', array('item_id' => $recipe->getItem()->getId()));
            }
        }

        return $this->render('AdminBundle:Form:create_recipe.html.twig', array('form' => $form->createView()));
    }
    /**
     * @Route("/edit-recipe/{item_id}", name="edit_recipe")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editRecipeAction(Request $request, $item_id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $recipe = $em->getRepository('IdleBundle:Recipe')->findOneBy(array('item' => $item_id));

        $originalCrafts = new ArrayCollection();

        // Create an ArrayCollection of the current Craft objects in the database
        foreach ($recipe->getCrafts() as $craft) {
            $originalCrafts->add($craft);
        }
        
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // remove the relationship between the craft and the Recipe
                /** @var Craft $craft */
                foreach ($originalCrafts as $craft) {
                    if ($recipe->getCrafts()->contains($craft) === false) {
                        // remove the Task from the Craft
                        $recipe->removeCraft($craft);

                        // if you wanted to delete the Craft entirely, you can also do that
                         $em->remove($craft);
                    }
                }

                $em->persist($recipe);
                $em->flush();

                $this->addFlash('success', "Changes saved");

                return $this->redirectToRoute('edit_recipe', array('item_id' => $recipe->getItem()->getId()));
            }
        }

        return $this->render('AdminBundle:Form:edit_recipe.html.twig', array('form' => $form->createView()));
    }
    /**
     * @Route("/remove-recipe/{item_id}", name="remove_recipe")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeRecipeAction(Request $request, $item_id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $recipe = $em->getRepository('IdleBundle:Recipe')->findOneBy(array('item' => $item_id));

        $em->remove($recipe);
        $em->flush();

        $this->addFlash('success', "Item removed");

        return $this->redirectToRoute('idle_generator');
    }
}
