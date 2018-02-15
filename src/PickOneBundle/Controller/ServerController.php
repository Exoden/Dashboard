<?php

namespace PickOneBundle\Controller;

use PickOneBundle\Entity\Answer;
use PickOneBundle\Entity\FavoriteQuestion;
use PickOneBundle\Entity\Question;
use PickOneBundle\Entity\UserAnswers;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerController extends Controller
{
    /**
     * @Route("/submit_question/{question_id}/answer/{answer_id}", name="submit_question")
     */
    public function ajaxSubmitQuestion(Request $request, $question_id, $answer_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Question $question */
        $question = $em->getRepository('PickOneBundle:Question')->find($question_id);
        if (!$question) {
            $this->addFlash('error', "Question not found");
            return new JsonResponse(array('error' => true, 'message' => "Question not found."));
        }

        /** @var Answer $answer */
        $answer = $em->getRepository('PickOneBundle:Answer')->findOneBy(array('question' => $question, 'id' => $answer_id));
        if (!$answer) {
            $this->addFlash('error', "The answer selected doesn't correspond to the question.");
            return new JsonResponse(array('error' => true, 'message' => "The answer selected doesn't correspond to the question."));
        }

        $user_answer = $em->getRepository('PickOneBundle:UserAnswers')->findOneBy(array('user' => $user, 'question' => $question));
        if ($user_answer) {
            $this->addFlash('error', "You already answered this question.");
            return new JsonResponse(array('error' => true, 'message' => "You already answered this question."));
        }
        else {
            $user_answer = new UserAnswers();
            $user_answer->setUser($user);
            $user_answer->setQuestion($question);
            $em->persist($user_answer);

            $question->setNbVotes($question->getNbVotes() + 1);
            $em->persist($question);

            $answer->setNbVotes($answer->getNbVotes() + 1);
            $em->persist($answer);
            $em->flush();
//            $this->addFlash('success', "Answer added");
        }

        return new JsonResponse(array(
            'success' => true,
            'message' => "Question \"" . $question->getTitle() . "\" has been answered.",
            'view' => $this->renderViewTemplateQuestionAction($question_id)
            ));
    }

    public function renderViewTemplateQuestionAction($question_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Question $question */
        $question = $em->getRepository('PickOneBundle:Question')->find($question_id);

        $answers = $em->getRepository('PickOneBundle:Answer')->findBy(array('question' => $question_id));

        /** @var UserAnswers $user_answer */
        $user_answer = $em->getRepository('PickOneBundle:UserAnswers')->findOneBy(array('user' => $user, 'question' => $question_id));
        if ($user_answer)
            return $this->renderView('PickOneBundle:Default:template_question_answered.html.twig', array('question' => $question, 'answers' => $answers));
        else
            return $this->renderView('PickOneBundle:Default:template_question_asked.html.twig', array('question' => $question, 'answers' => $answers));
    }

    /**
     * @Route("/add_favorite/{question_id}", name="add_favorite")
     */
    public function ajaxAddFavorite(Request $request, $question_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Question $question */
        $question = $em->getRepository('PickOneBundle:Question')->find($question_id);
        if (!$question) {
            $this->addFlash('error', "Question not found");
            return new JsonResponse(array('error' => true, 'message' => "Question not found."));
        }

        /** @var FavoriteQuestion $favorite */
        $favorite = $em->getRepository('PickOneBundle:FavoriteQuestion')->findOneBy(array('user' => $user, 'question' => $question));
        if ($favorite) {
            $this->addFlash('warning', "You are already added this Question to favorites");
            return new JsonResponse(array('error' => true, 'message' => "You are already added this Question to favorites."));
        }
        else {
            $favorite = new FavoriteQuestion();
            $favorite->setUser($user);
            $favorite->setQuestion($question);
            $em->persist($favorite);
            $em->flush();
            $this->addFlash('success', "Question added to favorites");
        }

        return new JsonResponse(array('success' => true, 'message' => "Question \"" . $question->getTitle() . "\" has been added to your favorites."));
    }

    /**
     * @Route("/remove_favorite/{question_id}", name="remove_favorite")
     */
    public function ajaxRemoveFavorite(Request $request, $question_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Question $question */
        $question = $em->getRepository('PickOneBundle:Question')->find($question_id);
        if (!$question) {
            $this->addFlash('error', "Question not found");
            return new JsonResponse(array('error' => true, 'message' => "Question not found."));
        }

        /** @var FavoriteQuestion $favorite */
        $favorite = $em->getRepository('PickOneBundle:FavoriteQuestion')->findOneBy(array('user' => $user, 'question' => $question));
        if (!$favorite) {
            $this->addFlash('error', 'This Question is not in your favorites.');
            return new JsonResponse(array('error' => true, 'message' => "This Question is not in your favorites."));
        }
        else {
            $em->remove($favorite);
            $em->flush();
            $this->addFlash('success', "Favorite removed");
        }

        return new JsonResponse(array('success' => true, 'message' => "Question \"" . $question->getTitle() . "\" has been removed from your favorites."));
    }
}
