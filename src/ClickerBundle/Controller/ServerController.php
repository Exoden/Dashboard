<?php

namespace ClickerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerController extends Controller
{

    /**
     * @Route("/switch-hero/{hero_id}", name="switch_hero")
     */
    public function ajaxChangeSelectedCharacter(Request $request, $hero_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $heroes = $em->getRepository('ClickerBundle:Hero')->findBy(array('user' => $user));

        foreach ($heroes as $hero) {
            if ($hero->getId() == $hero_id)
                $hero->setIsSelected(true);
            else
                $hero->setIsSelected(false);
            $em->persist($hero);
            $em->flush();
        }

        $hero = $em->getRepository('ClickerBundle:Hero')->findOneBy(array('user' => $user, 'isSelected' => true));

        return new JsonResponse(array('success' => true, 'selected_hero_id' => $hero->getId()));
    }

    /**
     * @Route("/exp-hero/{hero_id}", name="exp_hero")
     */
    public function ajaxHeroEarnExp(Request $request, $hero_id)
    {
        $exp = $request->request->get('exp');

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hero = $em->getRepository('ClickerBundle:Hero')->find($hero_id);

        $hero->setExperience($hero->getExperience() + $exp);

        $next_tick = $em->getRepository('ClickerBundle:ExperienceTable')->find($hero->getLevel())->getExperience();

        if ($hero->getExperience() >= $next_tick) {
            $hero->setExperience($hero->getExperience() - $next_tick);
            $hero->setLevel($hero->getLevel() + 1);
            $next_tick = $em->getRepository('ClickerBundle:ExperienceTable')->find($hero->getLevel())->getExperience();
        }

        $em->persist($hero);
        $em->flush();

        return new JsonResponse(array('success' => true, 'hero_id' => $hero->getId(), 'level' => $hero->getLevel(), 'experience' => $hero->getExperience(), 'next_level' => $next_tick));
    }
}
