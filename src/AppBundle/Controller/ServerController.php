<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerController extends Controller
{
    /**
     * @Route("/add_friend/{friend_id}", name="add_friend")
     */
    public function ajaxAddReadings(Request $request, $friend_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var User $friend */
        $friend = $em->getRepository('AppBundle:User')->find($friend_id);
        if (!$friend) {
            $this->addFlash('error', "User not found.");
            return new JsonResponse(array('error' => true, 'message' => "User not found."));
        }

        if ($user == $friend) {
            $this->addFlash('error', "Don't be narcissistic.");
            return new JsonResponse(array('error' => true, 'message' => "Don't be narcissistic."));
        }
        if ($user->getFriends()->contains($friend)) {
            $this->addFlash('error', "You are already friends.");
            return new JsonResponse(array('error' => true, 'message' => "You are already friends."));
        }

        $user->addFriend($friend);
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', "Friend added");

        return new JsonResponse(array('success' => true, 'message' => "User \"" . $friend->getUsername() . "\" has been added to your friends."));
    }
}
