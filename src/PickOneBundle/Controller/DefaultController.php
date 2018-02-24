<?php

namespace PickOneBundle\Controller;

use AppBundle\Entity\User;
use Knp\Component\Pager\Paginator;
use PickOneBundle\Entity\Answer;
use PickOneBundle\Entity\Question;
use PickOneBundle\Entity\UserAnswers;
use PickOneBundle\Form\QuestionCreateType;
use PickOneBundle\Form\QuestionEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use \Statickidz\GoogleTranslate;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage_pickone")
     */
    public function indexAction(Request $request)
    {
        return $this->redirect('questions');
//        return $this->render('PickOneBundle:Default:homepage.html.twig');
    }

    /**
     * @Route("/questions", name="questions")
     */
    public function questionsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $paginator  = $this->get('knp_paginator');
        $translator = new GoogleTranslate();

        /** @var User $user */
        $user = $this->getUser();

        /** @var Question $questions */
        $questions = $em->getRepository('PickOneBundle:Question')->getQuestionsWithGenre(null, true, ($request->query->get('new_only')) ? $user : null);

        $pagination = $paginator->paginate(
            $questions, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );

        /** @var Question $item */
        foreach ($pagination as $item) {
            if ($item->getLanguage()->getCode() != $user->getLocale()) {
                $item->setTitle($translator->translate($item->getLanguage()->getCode(), $user->getLocale(), $item->getTitle()));
                /** @var Answer $answer */
                foreach ($item->getAnswers() as $answer) {
                    $answer->setContent($translator->translate($item->getLanguage()->getCode(), $user->getLocale(), $answer->getContent()));
                }
            }
        }

        $genres = $em->getRepository('PickOneBundle:QuestionGenre')->getAllGenresSortedBy('ASC');

        return $this->render('PickOneBundle:Default:questions.html.twig', array('questions' => $pagination, 'genres' => $genres, 'title' => "Questions"));
    }

    /**
     * @Route("/questions/genre/{genre_name}", name="questions_genre")
     */
    public function questionsGenreAction(Request $request, $genre_name)
    {
        $em = $this->getDoctrine()->getManager();
        $paginator  = $this->get('knp_paginator');
        $translator = new GoogleTranslate();

        /** @var User $user */
        $user = $this->getUser();

        $genre_selected = $em->getRepository('PickOneBundle:QuestionGenre')->findOneBy(array('name' => $genre_name));
        if (!$genre_selected) {
            throw $this->createNotFoundException();
        }

        $questions = $em->getRepository('PickOneBundle:Question')->getQuestionsWithGenre($genre_selected, true, ($request->query->get('new_only')) ? $user : null);
        $pagination = $paginator->paginate(
            $questions, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );

        /** @var Question $item */
        foreach ($pagination as $item) {
            if ($item->getLanguage()->getCode() != $user->getLocale()) {
                $item->setTitle($translator->translate($item->getLanguage()->getCode(), $user->getLocale(), $item->getTitle()));
                /** @var Answer $answer */
                foreach ($item->getAnswers() as $answer) {
                    $answer->setContent($translator->translate($item->getLanguage()->getCode(), $user->getLocale(), $answer->getContent()));
                }
            }
        }

        /** @var Question $item */
        foreach ($pagination as $item) {
            if ($item->getLanguage()->getCode() != $user->getLocale()) {
                $item->setTitle($translator->translate($item->getLanguage()->getCode(), $user->getLocale(), $item->getTitle()));
                /** @var Answer $answer */
                foreach ($item->getAnswers() as $answer) {
                    $answer->setContent($translator->translate($item->getLanguage()->getCode(), $user->getLocale(), $answer->getContent()));
                }
            }
        }

        $genres = $em->getRepository('PickOneBundle:QuestionGenre')->getAllGenresSortedBy('ASC');

        return $this->render('PickOneBundle:Default:questions.html.twig', array('questions' => $pagination, 'genres' => $genres, 'title' => "Question"));
    }

    /**
     * @Route("/my-favorites", name="my_favorites")
     */
    public function myFavoritesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $paginator  = $this->get('knp_paginator');
        $translator = new GoogleTranslate();

        /** @var User $user */
        $user = $this->getUser();

        /** @var Question $questions */
        $questions = $em->getRepository('PickOneBundle:FavoriteQuestion')->getFavoritesFromUser($user);
        $pagination = $paginator->paginate(
            $questions, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );

        /** @var Question $item */
        foreach ($pagination as $item) {
            if ($item->getLanguage()->getCode() != $user->getLocale()) {
                $item->setTitle($translator->translate($item->getLanguage()->getCode(), $user->getLocale(), $item->getTitle()));
                /** @var Answer $answer */
                foreach ($item->getAnswers() as $answer) {
                    $answer->setContent($translator->translate($item->getLanguage()->getCode(), $user->getLocale(), $answer->getContent()));
                }
            }
        }

        $genres = $em->getRepository('PickOneBundle:QuestionGenre')->getAllGenresSortedBy('ASC');

        return $this->render('PickOneBundle:Default:questions.html.twig', array('questions' => $pagination, 'genres' => $genres, 'title' => "My Favorites"));
    }

    /**
     * @Route("/my-questions", name="my_questions")
     */
    public function myQuestionsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $paginator  = $this->get('knp_paginator');
        $translator = new GoogleTranslate();

        /** @var User $user */
        $user = $this->getUser();

        /** @var Question $questions */
        $questions = $em->getRepository('PickOneBundle:Question')->getQuestionsFromAuthor($user);
        $pagination = $paginator->paginate(
            $questions, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );

        /** @var Question $item */
        foreach ($pagination as $item) {
            if ($item->getLanguage()->getCode() != $user->getLocale()) {
                $item->setTitle($translator->translate($item->getLanguage()->getCode(), $user->getLocale(), $item->getTitle()));
                /** @var Answer $answer */
                foreach ($item->getAnswers() as $answer) {
                    $answer->setContent($translator->translate($item->getLanguage()->getCode(), $user->getLocale(), $answer->getContent()));
                }
            }
        }

        $genres = $em->getRepository('PickOneBundle:QuestionGenre')->getAllGenresSortedBy('ASC');

        return $this->render('PickOneBundle:Default:questions.html.twig', array('questions' => $pagination, 'genres' => $genres, 'title' => "My Questions"));
    }

    /**
     * @Route("/question/create", name="create_question")
     */
    public function createQuestionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Session $session */
        $session = $request->getSession();

        /** @var User $user */
        $user = $this->getUser();

        $question = new Question();
        $form = $this->createForm(QuestionCreateType::class, $question);
        $form->handleRequest($request);

        $check_errors = array('genres');

        // Errors from the submitted form, added in FlashBag and form Errors
        foreach ($check_errors as $ce) {
            $this->makeFormErrorAndFlash($form, $ce);
        }

        if ($form->isSubmitted()) {
            if (count($form->get('genres')->getData()) == 0)
                $this->addFlash('genres', "You must select at least one genre.");
            foreach ($check_errors as $ce) {
                if ($session->getFlashBag()->has($ce))
                    return $this->redirectToRoute('create_question');
            }

            if ($form->isValid()) {
                $question->setAuthor($user);
                $em->persist($question);
                $em->flush();
                $this->addFlash('success', "New Question saved");
                return $this->redirectToRoute('my_questions');
            }
        }
        return $this->render('PickOneBundle:Form:create_question.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/question/{question_id}/edit", name="edit_question")
     */
    public function editQuestionAction(Request $request, $question_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Session $session */
        $session = $request->getSession();

        /** @var User $user */
        $user = $this->getUser();

        /** @var Question $question */
        $question = $em->getRepository('PickOneBundle:Question')->find($question_id);
        if (!$question) {
            throw $this->createNotFoundException();
        }
        if ($question->getAuthor() != $user) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(QuestionEditType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Valid form
            if ($form->isValid()) {
                $em->persist($question);
                $em->flush();
                $this->addFlash('success', "Changes saved");
            }
        }

        return $this->render('PickOneBundle:Form:edit_question.html.twig', array('question' => $question, 'form' => $form->createView()));
    }

    /**
     * @Route("/render-template-question/{question_id}", name="render_template_question")
     */
    public function renderTemplateQuestionAction(Request $request, $question_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        /** @var Question $question */
        $question = $em->getRepository('PickOneBundle:Question')->find($question_id);

        $answers = $em->getRepository('PickOneBundle:Answer')->findBy(array('question' => $question_id));

        /** @var UserAnswers $user_answer */
        $user_answer = $em->getRepository('PickOneBundle:UserAnswers')->findOneBy(array('user' => $user, 'question' => $question_id));

        if ($user_answer)
            return $this->render('PickOneBundle:Default:template_question_answered.html.twig', array('question' => $question, 'answers' => $answers, 'title' => $request->query->get('title')));
        else
            return $this->render('PickOneBundle:Default:template_question_asked.html.twig', array('question' => $question, 'answers' => $answers, 'title' => $request->query->get('title')));
    }
    
    public function makeFormErrorAndFlash($form, $error_name)
    {
        $session = $this->get('session');

        if ($session->getFlashBag()->has($error_name)) {
            $error = $session->getFlashBag()->get($error_name);
            $msgError = new FormError($error[0]);
            $form->get($error_name)->addError($msgError);
            $this->addFlash('error', $error[0]);
        }
    }
}
