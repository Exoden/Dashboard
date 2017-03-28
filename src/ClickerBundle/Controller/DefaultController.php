<?php

namespace ClickerBundle\Controller;

use ClickerBundle\Entity\Characteristics;
use ClickerBundle\Entity\Hero;
use AppBundle\Entity\User;
use ClickerBundle\Form\HeroType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage_clicker")
     */
    public function indexAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', "Access denied");
            return $this->redirectToRoute('homepage');
        }

        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $heroes = $em->getRepository('ClickerBundle:Hero')->findBy(array('user' => $user->getId()));

        $stuff = array();
        /** @var Hero $hero */
        foreach ($heroes as $key => $hero) {
            $stuff[$key]['weapon'] = $em->getRepository('ClickerBundle:Stuff')->findOneBy(array('hero' => $hero->getId(), 'type' => $em->getRepository('ClickerBundle:StuffType')->findOneBy(array('name' => "Weapon"))->getId()));
            $stuff[$key]['offhand'] = $em->getRepository('ClickerBundle:Stuff')->findOneBy(array('hero' => $hero->getId(), 'type' => $em->getRepository('ClickerBundle:StuffType')->findOneBy(array('name' => "OffHand"))->getId()));
            $stuff[$key]['artifact'] = $em->getRepository('ClickerBundle:Stuff')->findOneBy(array('hero' => $hero->getId(), 'type' => $em->getRepository('ClickerBundle:StuffType')->findOneBy(array('name' => "Artifact"))->getId()));
            $stuff[$key]['armor'] = $em->getRepository('ClickerBundle:Stuff')->findOneBy(array('hero' => $hero->getId(), 'type' => $em->getRepository('ClickerBundle:StuffType')->findOneBy(array('name' => "Armor"))->getId()));
            $stuff[$key]['helmet'] = $em->getRepository('ClickerBundle:Stuff')->findOneBy(array('hero' => $hero->getId(), 'type' => $em->getRepository('ClickerBundle:StuffType')->findOneBy(array('name' => "Helmet"))->getId()));
            $stuff[$key]['gloves'] = $em->getRepository('ClickerBundle:Stuff')->findOneBy(array('hero' => $hero->getId(), 'type' => $em->getRepository('ClickerBundle:StuffType')->findOneBy(array('name' => "Gloves"))->getId()));
            $stuff[$key]['jewel'] = $em->getRepository('ClickerBundle:Stuff')->findOneBy(array('hero' => $hero->getId(), 'type' => $em->getRepository('ClickerBundle:StuffType')->findOneBy(array('name' => "Jewel"))->getId()));
        }

        if (count($heroes) == 0) {
            $hero = new Hero();
            $form = $this->createForm(HeroType::class, $hero);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var Characteristics $charac */
                $charac = new Characteristics();
                $charac->setArmor(0);
                $charac->setAttackDelay(3);
                $charac->setDamageMinimum(1);
                $charac->setDamageMaximum(1);
                $charac->setDodge(0);
                $charac->setHealth(100);
                $charac->setHitPrecision(75);
                $charac->setSpeed(1);
                $em->persist($charac);

                $hero->setCharacteristics($charac);
                $hero->setAge(15);
                $hero->setCurrentHealth(100);
                $hero->setFieldLevel(1);
                $hero->setFieldMaxLevel(1);
                $hero->setIsRested(true);
                $hero->setRestStartTime(null);
                $hero->setRestEndTime(null);
                $hero->setUser($user);
                $em->persist($hero);

                $em->flush();

                return $this->redirect($this->generateUrl($request->attributes->get('_route')));
            }
            return $this->render('ClickerBundle:Default:homepage.html.twig', array('heroes' => $heroes, 'form' => $form->createView()));
        }

        return $this->render('ClickerBundle:Default:homepage.html.twig', array('heroes' => $heroes, 'stuff' => $stuff));
    }

    /**
     * @Route("/armory", name="armory")
     */
    public function armoryAction(Request $request)
    {
        return $this->render('ClickerBundle:Default:armory.html.twig');
    }

    /**
     * @Route("/armory/equip", name="armory_equip")
     */
    public function armoryEquipAction(Request $request)
    {
        return $this->render('ClickerBundle:Default:armory_equip.html.twig');
    }

    /**
     * @Route("/armory/upgrade", name="armory_upgrade")
     */
    public function armoryUpgradeAction(Request $request)
    {
        return $this->render('ClickerBundle:Default:armory_upgrade.html.twig');
    }

    /**
     * @Route("/guild", name="guild")
     */
    public function guildAction(Request $request)
    {
        return $this->render('ClickerBundle:Default:guild.html.twig');
    }

    /**
     * @Route("/guild/mainpage", name="guild_mainpage")
     */
    public function guildMainpageAction(Request $request)
    {
        return $this->render('ClickerBundle:Default:guild_mainpage.html.twig');
    }

    /**
     * @Route("/guild/members", name="guild_members")
     */
    public function guildMembersAction(Request $request)
    {
        return $this->render('ClickerBundle:Default:guild_members.html.twig');
    }

    /**
     * @Route("/guild/raids", name="guild_raids")
     */
    public function guildRaidsAction(Request $request)
    {
        return $this->render('ClickerBundle:Default:guild_raids.html.twig');
    }

    /**
     * @Route("/dungeons", name="dungeons")
     */
    public function dungeonAction(Request $request)
    {
        return $this->render('ClickerBundle:Default:dungeons.html.twig');
    }
}
