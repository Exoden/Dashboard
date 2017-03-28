<?php

namespace ClickerBundle\Controller;

use AppBundle\Entity\User;
use ClickerBundle\Entity\BattleHistory;
use ClickerBundle\Entity\Hero;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerController extends Controller
{
    /**
     * @Route("/battle-history/{hero_id}", name="battle_history")
     */
    public function ajaxChangeSelectedCharacter(Request $request, $hero_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        /** @var Hero $hero */
        $hero = $em->getRepository('ClickerBundle:Hero')->findOneBy(array('id' => $hero_id, 'user' => $user));
        if (!$hero) {
            $this->addFlash('error', "Hero not found.");
            return new JsonResponse(array('success' => false));
        }

        $last_battle = $em->getRepository('ClickerBundle:BattleHistory')->findOneBy(array('hero' => $hero));
        if (!$last_battle) {
            $last_battle = new BattleHistory();
            $last_battle->setHero($hero);
        }

        $historic = json_decode($last_battle->getHistoric(), true);
        $now = microtime(true);
        $battle_history = array();
        $time = 0;
        $until = (60 * 5); // 5 minutes

        // Get a piece of the previous historic
        if ($historic && end($historic)['time'] > $now) {
            foreach ($historic as $key => $histo) {
                if ($histo['time'] < $now) {
                    $battle_history = array_splice($historic, $key);
                    $time = ((count($battle_history) + 1) * $hero->getCharacteristics()->getAttackDelay()); // +1 to skip the current cell, because we keep it and want to generate the following
                    break;
                }
            }
            $now = end($historic)['time']; // now generate from the end of the historic
        }

        // Fill the historic
        while ($time <= $until) {
            $damage = rand($hero->getCharacteristics()->getDamageMinimum(), $hero->getCharacteristics()->getDamageMaximum());
            $battle_history[] = array('type' => "HIT", 'time' => $now + $time, 'damage' => $damage);

            $time += $hero->getCharacteristics()->getAttackDelay();
        }
        $last_battle->setHistoric(json_encode($battle_history));
        $em->persist($last_battle);
        $em->flush();

        return new JsonResponse(array('success' => true, 'battle_history' => $battle_history));
    }

//    /**
//     * @Route("/switch-hero/{hero_id}", name="switch_hero")
//     */
//    public function ajaxChangeSelectedCharacter(Request $request, $hero_id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $heroes = $em->getRepository('ClickerBundle:Hero')->findBy(array('user' => $user));
//
//        foreach ($heroes as $hero) {
//            if ($hero->getId() == $hero_id)
//                $hero->setIsSelected(true);
//            else
//                $hero->setIsSelected(false);
//            $em->persist($hero);
//            $em->flush();
//        }
//
//        $hero = $em->getRepository('ClickerBundle:Hero')->findOneBy(array('user' => $user, 'isSelected' => true));
//
//        return new JsonResponse(array('success' => true, 'selected_hero_id' => $hero->getId()));
//    }

//    /**
//     * @Route("/exp-hero/{hero_id}", name="exp_hero")
//     */
//    public function ajaxHeroEarnExp(Request $request, $hero_id)
//    {
//        $exp = $request->request->get('exp');
//
//        $em = $this->getDoctrine()->getManager();
//
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $hero = $em->getRepository('ClickerBundle:Hero')->find($hero_id);
//
//        $hero->setExperience($hero->getExperience() + $exp);
//
//        $next_tick = $em->getRepository('ClickerBundle:ExperienceTable')->find($hero->getLevel())->getExperience();
//
//        if ($hero->getExperience() >= $next_tick) {
//            $hero->setExperience($hero->getExperience() - $next_tick);
//            $hero->setLevel($hero->getLevel() + 1);
//            $next_tick = $em->getRepository('ClickerBundle:ExperienceTable')->find($hero->getLevel())->getExperience();
//        }
//
//        $em->persist($hero);
//        $em->flush();
//
//        return new JsonResponse(array('success' => true, 'hero_id' => $hero->getId(), 'level' => $hero->getLevel(), 'experience' => $hero->getExperience(), 'next_level' => $next_tick));
//    }
}
