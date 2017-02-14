<?php

namespace StoryTellBundle\Controller;

use StoryTellBundle\Entity\Readings;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerController extends Controller
{
    /**
     * @Route("/add_readings/{story_id}", name="add_readings")
     */
    public function ajaxAddReadings(Request $request, $story_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $story = $em->getRepository('StoryTellBundle:Story')->find($story_id);
        if (!$story) {
            $this->addFlash('error', "Story not found");
            return new JsonResponse(array('error' => true, 'message' => "Story not found."));
        }

        $reading = $em->getRepository('StoryTellBundle:Readings')->findOneBy(array('user' => $user, 'story' => $story));
        if ($reading) {
            $this->addFlash('warning', "You are already reading this Story");
            return new JsonResponse(array('error' => true, 'message' => "You are already reading this Story."));
        }
        else {
            $reading = new Readings();
            $reading->setUser($user);
            $reading->setStory($story);
            $storyChapter = $em->getRepository('StoryTellBundle:StoryChapter')->getFirstChapter($story);
            $reading->setStoryChapter($storyChapter);
            $reading->setStoryContent($em->getRepository('StoryTellBundle:StoryContent')->getFirstContent($storyChapter));
            $reading->setIsFinished(false);
            $em->persist($reading);
            $em->flush();
            $this->addFlash('success', "Story added");
        }

        return new JsonResponse(array('success' => true, 'message' => "Story \"" . $story->getTitle() . "\" has been added to your readings."));
    }

    /**
     * @Route("/remove_readings/{story_id}", name="remove_readings")
     */
    public function ajaxRemoveReadings(Request $request, $story_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $story = $em->getRepository('StoryTellBundle:Story')->find($story_id);
        if (!$story) {
            $this->addFlash('error', "Story not found");
            return new JsonResponse(array('error' => true, 'message' => "Story not found."));
        }

        $reading = $em->getRepository('StoryTellBundle:Readings')->findOneBy(array('user' => $user, 'story' => $story));
        if (!$reading) {
            $this->addFlash('error', 'You are not reading this Story');
            return new JsonResponse(array('error' => true, 'message' => "You are not reading this Story."));
        }
        else {
            $em->remove($reading);
            $em->flush();
            $this->addFlash('success', "Story removed");
        }

        return new JsonResponse(array('success' => true, 'message' => "Story \"" . $story->getTitle() . "\" has been removed from your readings."));
    }
}
