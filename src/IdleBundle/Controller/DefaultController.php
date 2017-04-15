<?php

namespace IdleBundle\Controller;

use IdleBundle\Entity\Characteristics;
use IdleBundle\Entity\Enemy;
use IdleBundle\Entity\Hero;
use AppBundle\Entity\User;
use IdleBundle\Entity\Inventory;
use IdleBundle\Entity\PossessedRecipes;
use IdleBundle\Entity\Target;
use IdleBundle\Entity\TypeStuff;
use IdleBundle\Form\HeroType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage_idle")
     */
    public function indexAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', "Access denied");
            return $this->redirectToRoute('homepage');
        }

        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();
        $heroes = $em->getRepository('IdleBundle:Hero')->findBy(array('user' => $user->getId()));

        $equipments = array();
        /** @var Hero $hero */
        foreach ($heroes as $key => $hero) {
            $equipments[$key]['weapon'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Weapon"))->getId());
            $equipments[$key]['offhand'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "OffHand"))->getId());
            $equipments[$key]['artifact'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Artifact"))->getId());
            $equipments[$key]['armor'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Armor"))->getId());
            $equipments[$key]['helmet'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Helmet"))->getId());
            $equipments[$key]['gloves'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Gloves"))->getId());
            $equipments[$key]['jewel'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Jewel"))->getId());
        }

        if (count($heroes) == 0) {
            $hero = new Hero();
            $form = $this->createForm(HeroType::class, $hero);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var Characteristics $charac */
                $charac = new Characteristics();
                $charac->setArmor(0);
                $charac->setAttackDelay(2.5);
                $charac->setDamageMinimum(1);
                $charac->setDamageMaximum(1);
                $charac->setDodge(0);
                $charac->setHealth(100);
                $charac->setHitPrecision(75);
                $charac->setSpeed(1);
                $em->persist($charac);

                /** @var Enemy $enemy */
                $enemy = $em->getRepository('IdleBundle:Enemy')->find(1);
                $target = new Target();
                $target->setCurrentHealth($enemy->getCharacteristics()->getHealth());
                $target->setEnemy($enemy);
                $em->persist($target);

                $hero->setCharacteristics($charac);
                $hero->setAge(15);
                $hero->setCurrentHealth(100);
                $hero->setFieldLevel(1);
                $hero->setFieldMaxLevel(1);
                $hero->setIsRested(true);
                $hero->setRestStartTime(null);
                $hero->setRestEndTime(null);
                $hero->setUser($user);
                $hero->setTarget($target);
                $em->persist($hero);

                $em->flush();

                return $this->redirect($this->generateUrl($request->attributes->get('_route')));
            }
            return $this->render('IdleBundle:Default:homepage.html.twig', array('heroes' => $heroes, 'form' => $form->createView()));
        }
        else {
            // TODO : apply past battle historic
        }

        return $this->render('IdleBundle:Default:homepage.html.twig', array('heroes' => $heroes, 'equipments' => $equipments));
    }

    /**
     * @Route("/inventory", name="inventory")
     */
    public function armoryInventoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $user = $this->getUser();

        $inventory = $em->getRepository('IdleBundle:Inventory')->findBy(array('user' => $user));

        $possessed_recipes = $em->getRepository('IdleBundle:PossessedRecipes')->findBy(array('user' => $user));

        $tab_recipe = array();
        $i = 0;
        /** @var PossessedRecipes $pr */
        foreach ($possessed_recipes as $pr) {
            $tab_recipe[$i]['recipe'] = $pr->getRecipe();
            $tab_recipe[$i]['craft'] = array();
            $tab_recipe[$i]['craft'] = $em->getRepository('IdleBundle:Craft')->findBy(array('recipe' => $pr->getRecipe()));

            $i++;
        }

        $tab_inv = array();
        $i = 0;
        /** @var Inventory $inv */
        foreach ($inventory as $inv) {
            if ($inv->getItem()->getTypeItem()->getName() == "Equipment")
                $tab_inv[$i]['obj'] = $em->getRepository('IdleBundle:Stuff')->findOneBy(array('item' => $inv->getItem()));
            else if ($inv->getItem()->getTypeItem()->getName() == "Resource")
                $tab_inv[$i]['obj'] = $em->getRepository('IdleBundle:Resource')->findOneBy(array('item' => $inv->getItem()));
            else if ($inv->getItem()->getTypeItem()->getName() == "Recipe")
                $tab_inv[$i]['obj'] = $em->getRepository('IdleBundle:Recipe')->findOneBy(array('item' => $inv->getItem()));

            $tab_inv[$i]['quantity'] = $inv->getQuantity();

            $i++;
        }

        return $this->render('IdleBundle:Default:inventory.html.twig', array('recipes' => $tab_recipe, 'inventory' => $tab_inv));
    }

    /**
     * @Route("/equipment", name="equipment")
     */
    public function armoryEquipmentAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $user = $this->getUser();
        $heroes = $em->getRepository('IdleBundle:Hero')->findBy(array('user' => $user->getId()));

        $list_type_stuff = $em->getRepository('IdleBundle:TypeStuff')->findAll();

        $equipments = array();
        /** @var Hero $hero */
        foreach ($heroes as $key => $hero) {
            /** @var TypeStuff $lts */
            foreach ($list_type_stuff as $lts) {
                $equipments[$key][$lts->getName()] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $lts->getId());
            }
//            $equipments[$key]['weapon'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Weapon"))->getId());
//            $equipments[$key]['offhand'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "OffHand"))->getId());
//            $equipments[$key]['artifact'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Artifact"))->getId());
//            $equipments[$key]['armor'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Armor"))->getId());
//            $equipments[$key]['helmet'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Helmet"))->getId());
//            $equipments[$key]['gloves'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Gloves"))->getId());
//            $equipments[$key]['jewel'] = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero, $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Jewel"))->getId());
        }

        $inventory_stuffs = $em->getRepository('IdleBundle:Inventory')->getStuffs($user);

        $tab_stuffs = array();
        $i = 0;
        /** @var Inventory $inv */
        foreach ($inventory_stuffs as $inv) {
            $tab_stuffs[$i]['obj'] = $em->getRepository('IdleBundle:Stuff')->findOneBy(array('item' => $inv->getItem()));;

            $tab_stuffs[$i]['quantity'] = $inv->getQuantity();

            $i++;
        }


        return $this->render('IdleBundle:Default:equipment.html.twig', array('heroes' => $heroes, 'equipments' => $equipments, 'stuffs' => $tab_stuffs));
    }

    /**
     * @Route("/guild", name="guild")
     */
    public function guildAction(Request $request)
    {
        return $this->render('IdleBundle:Default:guild.html.twig');
    }

    /**
     * @Route("/guild/mainpage", name="guild_mainpage")
     */
    public function guildMainpageAction(Request $request)
    {
        return $this->render('IdleBundle:Default:guild_mainpage.html.twig');
    }

    /**
     * @Route("/guild/members", name="guild_members")
     */
    public function guildMembersAction(Request $request)
    {
        return $this->render('IdleBundle:Default:guild_members.html.twig');
    }

    /**
     * @Route("/guild/raids", name="guild_raids")
     */
    public function guildRaidsAction(Request $request)
    {
        return $this->render('IdleBundle:Default:guild_raids.html.twig');
    }

    /**
     * @Route("/dungeons", name="dungeons")
     */
    public function dungeonAction(Request $request)
    {
        return $this->render('IdleBundle:Default:dungeons.html.twig');
    }
}
