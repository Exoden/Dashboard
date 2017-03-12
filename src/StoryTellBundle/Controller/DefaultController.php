<?php

namespace StoryTellBundle\Controller;

use StoryTellBundle\Entity\Readings;
use StoryTellBundle\Entity\Story;
use StoryTellBundle\Entity\StoryChapter;
use StoryTellBundle\Entity\StoryContent;
use StoryTellBundle\Form\StoryChapterCreateType;
use StoryTellBundle\Form\StoryChapterEditType;
use StoryTellBundle\Form\StoryContentType;
use StoryTellBundle\Form\StoryCreateType;
use StoryTellBundle\Form\StoryEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage_storytell")
     */
    public function indexAction(Request $request)
    {
        return $this->render('StoryTellBundle:Default:homepage.html.twig');
    }

    /**
     * @Route("/story/create", name="create_story")
     */
    public function createStoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Session $session */
        $session = $request->getSession();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $story = new Story();
        $form = $this->createForm(StoryCreateType::class, $story);
        $form->handleRequest($request);

        $check_errors = array('title', 'description', 'genres');

        // Errors from the submitted form, added in FlashBag and form Errors
        foreach ($check_errors as $ce) {
            $this->makeFormErrorAndFlash($form, $ce);
        }

        if ($form->isSubmitted()) {
            $ctrl_url = array('title', 'description');
            foreach ($ctrl_url as $ce) {
                $this->controlUrl($ce, $story);
            }
            if (count($form->get('genres')->getData()) == 0)
                $this->addFlash('genres', "You must select at least one genre.");
            foreach ($check_errors as $ce) {
                if ($session->getFlashBag()->has($ce))
                    return $this->redirectToRoute('create_story');
            }

            if ($form->isValid()) {
                $story->setAuthor($user);
                $story->setIsPublished(false);
                $story->setIsFinished(false);
                $em->persist($story);
                $em->flush();
                $this->addFlash('success', "New Story saved");
                return $this->redirectToRoute('edit_story', array('story_id' => $story->getId(), 'chapters' => null));
            }
        }
        return $this->render('StoryTellBundle:Form:create_story.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/story/{story_id}/edit", name="edit_story")
     */
    public function editStoryAction(Request $request, $story_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Session $session */
        $session = $request->getSession();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Story $story */
        $story = $em->getRepository('StoryTellBundle:Story')->find($story_id);
        if (!$story) {
            throw $this->createNotFoundException();
        }
        if ($story->getAuthor() != $user) {
            throw $this->createAccessDeniedException();
        }

        $chapters = $em->getRepository('StoryTellBundle:StoryChapter')->findBy(array('story' => $story));

        $form = $this->createForm(StoryEditType::class, $story);
        $form->handleRequest($request);

        $check_errors = array('title', 'description', 'isPublished', 'isFinished');

        // Errors from the submitted form, added in FlashBag and form Errors
        foreach ($check_errors as $ce) {
            $this->makeFormErrorAndFlash($form, $ce);
        }

        if ($form->isSubmitted()) {
            $ctrl_url = array('title', 'description');
            foreach ($ctrl_url as $ce) {
                $this->controlUrl($ce, $story);
            }
            // Control checkbox, and refresh if errors
            if ($form->get('isPublished')->getData() == true && (!$chapters || ($chapters && !$chapters[0]->getIsPublished())))
                $this->addFlash('isPublished', "There is no chapter published. You must publish at least one chapter before publishing the Story.");
            if ($form->get('isFinished')->getData() == true) {
                $are_all_chapters_published = true;
                /** @var StoryChapter $chapter */
                foreach ($chapters as $chapter) {
                    if (!$chapter->getIsPublished())
                        $are_all_chapters_published = false;
                }
                if (!$are_all_chapters_published)
                    $this->addFlash('isFinished', "All the chapters are not published. You must publish all the chapters to finish the Story.");
            }
            foreach ($check_errors as $ce) {
                if ($session->getFlashBag()->has($ce))
                    return $this->redirectToRoute('edit_story', array('story_id' => $story_id));
            }

            // Valid form
            if ($form->isValid()) {
                $em->persist($story);
                $em->flush();
                $this->addFlash('success', "Changes saved");
            }
        }

        return $this->render('StoryTellBundle:Form:edit_story.html.twig', array('story' => $story, 'chapters' => $chapters, 'form' => $form->createView()));
    }

    /**
     * @Route("/story/{story_id}/chapter/create", name="create_chapter")
     */
    public function createChapterAction(Request $request, $story_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Session $session */
        $session = $request->getSession();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Story $story */
        $story = $em->getRepository('StoryTellBundle:Story')->find($story_id);
        if (!$story) {
            throw $this->createNotFoundException();
        }
        if ($story->getAuthor() != $user) {
            throw $this->createAccessDeniedException();
        }

        $chapter = new StoryChapter();
        $form = $this->createForm(StoryChapterCreateType::class, $chapter);
        $form->handleRequest($request);

        $check_errors = array('title');

        // Errors from the submitted form, added in FlashBag and form Errors
        foreach ($check_errors as $ce) {
            $this->makeFormErrorAndFlash($form, $ce);
        }

        if ($form->isSubmitted()) {
            $ctrl_url = array('title');
            foreach ($ctrl_url as $ce) {
                $this->controlUrl($ce, $chapter);
            }
            foreach ($check_errors as $ce) {
                if ($session->getFlashBag()->has($ce))
                    return $this->redirectToRoute('create_chapter', array('story_id' => $story_id));
            }

            // Valid form
            if ($form->isValid()) {
                $chapter->setStory($story);
                $chapter->setChapter($em->getRepository('StoryTellBundle:Story')->getNbChapters($story) + 1);
                $chapter->setIsPublished(false);
                $em->persist($chapter);
                $em->flush();
                $this->addFlash('success', "New Chapter saved");
                return $this->redirectToRoute('edit_chapter', array('story_id' => $story->getId(), 'chapter_id' => $chapter->getId(), 'contents' => null));
            }
    }
        return $this->render('StoryTellBundle:Form:create_chapter.html.twig', array('story' => $story, 'form' => $form->createView()));
    }

    /**
     * @Route("/story/{story_id}/chapter/{chapter_id}/edit", name="edit_chapter")
     */
    public function editChapterAction(Request $request, $story_id, $chapter_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Session $session */
        $session = $request->getSession();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Story $story */
        $story = $em->getRepository('StoryTellBundle:Story')->find($story_id);
        if (!$story) {
            throw $this->createNotFoundException();
        }
        if ($story->getAuthor() != $user) {
            throw $this->createAccessDeniedException();
        }

        /** @var StoryChapter $chapter */
        $chapter = $em->getRepository('StoryTellBundle:StoryChapter')->find($chapter_id);
        if (!$chapter) {
            throw $this->createNotFoundException();
        }

        $contents = $em->getRepository('StoryTellBundle:StoryContent')->findBy(array('storyChapter' => $chapter));

        $form = $this->createForm(StoryChapterEditType::class, $chapter);
        $form->handleRequest($request);

        $check_errors = array('title', 'isPublished');

        // Errors from the submitted form, added in FlashBag and form Errors
        foreach ($check_errors as $ce) {
            $this->makeFormErrorAndFlash($form, $ce);
        }

        if ($form->isSubmitted()) {
            $ctrl_url = array('title');
            foreach ($ctrl_url as $ce) {
                $this->controlUrl($ce, $chapter);
            }
            // Control checkbox, and refresh if errors
            /** @var StoryChapter $next_chapter */
            $next_chapter = $em->getRepository('StoryTellBundle:Story')->getNextChapter($story, $chapter);
            if (!$contents && $form->get('isPublished')->getData() == true)
                $this->addFlash('isPublished', "There is no content published. You must write at least one page before publishing the chapter.");
            else if ($next_chapter && $next_chapter->getIsPublished() == true && $form->get('isPublished')->getData() == false)
                $this->addFlash('isPublished', "You can not unpublish this chapter since the next chapter is published.");
            foreach ($check_errors as $ce) {
                if ($session->getFlashBag()->has($ce))
                    return $this->redirectToRoute('edit_chapter', array('story_id' => $story_id, 'chapter_id' => $chapter_id));
            }

            // Valid form
            if ($form->isValid()) {
                if ($chapter->getChapter() == 1 && $form->get('isPublished')->getData() == false && $story->getIsPublished() == true) {
                    $story->setIsPublished(false);
                    $this->addFlash('warning', "The Story has been unpublished since there is no chapter published yet.");
                    $em->persist($story);
                }
                $em->persist($chapter);
                $em->flush();
                $this->addFlash('success', "Changes saved");
            }
        }

        return $this->render('StoryTellBundle:Form:edit_chapter.html.twig', array('story' => $story, 'chapter' => $chapter, 'contents' => $contents, 'form' => $form->createView()));
    }

    /**
     * @Route("/story/{story_id}/chapter/{chapter_id}/content/create", name="create_content")
     */
    public function createContentAction(Request $request, $story_id, $chapter_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Session $session */
        $session = $request->getSession();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Story $story */
        $story = $em->getRepository('StoryTellBundle:Story')->find($story_id);
        if (!$story) {
            throw $this->createNotFoundException();
        }
        if ($story->getAuthor() != $user) {
            throw $this->createAccessDeniedException();
        }

        /** @var StoryChapter $chapter */
        $chapter = $em->getRepository('StoryTellBundle:StoryChapter')->find($chapter_id);
        if (!$chapter) {
            throw $this->createNotFoundException();
        }

        $content = new StoryContent();
        $form = $this->createForm(StoryContentType::class, $content);
        $form->handleRequest($request);

        $check_errors = array('content');

        // Errors from the submitted form, added in FlashBag and form Errors
        foreach ($check_errors as $ce) {
            $this->makeFormErrorAndFlash($form, $ce);
        }

        if ($form->isSubmitted()) {
            $ctrl_url = array('content');
            foreach ($ctrl_url as $ce) {
                $this->controlUrl($ce, $content);
            }
            foreach ($check_errors as $ce) {
                if ($session->getFlashBag()->has($ce))
                    return $this->redirectToRoute('create_content', array('story_id' => $story_id, 'chapter_id' => $chapter_id));
            }

            if ($form->isValid()) {
                $content->setStoryChapter($chapter);
                $content->setPage($em->getRepository('StoryTellBundle:StoryChapter')->getNumberLastPage($chapter) + 1);
                $em->persist($content);
                $em->flush();
                $this->addFlash('success', "New Content saved");
                return $this->redirectToRoute('edit_content', array('story_id' => $story->getId(), 'chapter_id' => $chapter->getId(), 'content_id' => $content->getId()));
            }
        }
        return $this->render('StoryTellBundle:Form:create_content.html.twig', array('story' => $story, 'chapter' => $chapter, 'form' => $form->createView()));
    }

    /**
     * @Route("/story/{story_id}/chapter/{chapter_id}/content/{content_id}/edit", name="edit_content")
     */
    public function editContentAction(Request $request, $story_id, $chapter_id, $content_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Session $session */
        $session = $request->getSession();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Story $story */
        $story = $em->getRepository('StoryTellBundle:Story')->find($story_id);
        if (!$story) {
            throw $this->createNotFoundException();
        }
        if ($story->getAuthor() != $user) {
            throw $this->createAccessDeniedException();
        }

        /** @var StoryChapter $chapter */
        $chapter = $em->getRepository('StoryTellBundle:StoryChapter')->find($chapter_id);
        if (!$chapter) {
            throw $this->createNotFoundException();
        }

        /** @var StoryContent $content */
        $content = $em->getRepository('StoryTellBundle:StoryContent')->find($content_id);
        if (!$content) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(StoryContentType::class, $content);
        $form->handleRequest($request);

        $check_errors = array('content');

        // Errors from the submitted form, added in FlashBag and form Errors
        foreach ($check_errors as $ce) {
            $this->makeFormErrorAndFlash($form, $ce);
        }

        if ($form->isSubmitted()) {
            $ctrl_url = array('content');
            foreach ($ctrl_url as $ce) {
                $this->controlUrl($ce, $content);
            }
            foreach ($check_errors as $ce) {
                if ($session->getFlashBag()->has($ce))
                    return $this->redirectToRoute('edit_content', array('story_id' => $story_id, 'chapter_id' => $chapter_id, 'content_id' => $content_id));
            }

            // Valid form
            if ($form->isValid()) {
                $em->persist($content);
                $em->flush();
                $this->addFlash('success', "Changes saved");
            }
        }

        return $this->render('StoryTellBundle:Form:edit_content.html.twig', array('story' => $story, 'chapter' => $chapter, 'content' => $content, 'form' => $form->createView()));
    }

    /**
     * @Route("/stories", name="stories")
     */
    public function storiesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Story $story */
        $stories = $em->getRepository('StoryTellBundle:Story')->findBy(array('isPublished' => true));

        $story_tab = array();
        $i = 0;
        foreach ($stories as $story) {
            $story_tab[$i]['story'] = $story;
            $story_tab[$i]['genres'] = $story->getGenres();
            $i++;
        }

        $genres = $em->getRepository('StoryTellBundle:StoryGenre')->findAll();

        return $this->render('StoryTellBundle:Default:stories.html.twig', array('story_tab' => $story_tab, 'genres' => $genres));
    }

    /**
     * @Route("/stories/genre/{genre_name}", name="stories_genre")
     */
    public function storiesGenreAction(Request $request, $genre_name)
    {
        $em = $this->getDoctrine()->getManager();

        $genre_selected = $em->getRepository('StoryTellBundle:StoryGenre')->findOneBy(array('name' => $genre_name));
        if (!$genre_selected) {
            throw $this->createNotFoundException();
        }

        $stories = $em->getRepository('StoryTellBundle:Story')->getStoriesWithGenre($genre_selected);

        $story_tab = array();
        $i = 0;
        /** @var Story $story */
        foreach ($stories as $story) {
            $story_tab[$i]['story'] = $story;
            $story_tab[$i]['genres'] = $story->getGenres();
            $i++;
        }

        $genres = $em->getRepository('StoryTellBundle:StoryGenre')->findAll();

        return $this->render('StoryTellBundle:Default:stories.html.twig', array('story_tab' => $story_tab, 'genres' => $genres));
    }

    /**
     * @Route("/my_readings", name="my_readings")
     */
    public function myReadingsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $readings = $em->getRepository('StoryTellBundle:Readings')->getReadingsOfUser($user);

        $readings_tab = array();
        $i = 0;
        /** @var Readings $reading */
        foreach ($readings as $reading) {
            $readings_tab[$i]['reading'] = $reading;
            $readings_tab[$i]['new_chapter'] = ($reading->getIsFinished() && $em->getRepository('StoryTellBundle:Story')->getNbChapters($reading->getStory(), true) > $reading->getStoryChapter()->getChapter()) ? true : false;
            $i++;
        }

        return $this->render('StoryTellBundle:Default:my_readings.html.twig', array('readings_tab' => $readings_tab));
    }

    /**
     * @Route("/my_stories", name="my_stories")
     */
    public function myStoriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $stories = $em->getRepository('StoryTellBundle:Story')->findBy(array('author' => $user));

        $story_tab = array();
        $i = 0;
        foreach ($stories as $story) {
            $story_tab[$i]['story'] = $story;
            $story_tab[$i]['nb_chapters_published'] = $em->getRepository('StoryTellBundle:Story')->getNbChapters($story, true);
            $story_tab[$i]['nb_pages_published'] = $em->getRepository('StoryTellBundle:Story')->getNbPages($story, true);
            $story_tab[$i]['nb_chapters'] = $em->getRepository('StoryTellBundle:Story')->getNbChapters($story);
            $story_tab[$i]['nb_pages'] = $em->getRepository('StoryTellBundle:Story')->getNbPages($story);
            $i++;
        }

        return $this->render('StoryTellBundle:Default:my_stories.html.twig', array('story_tab' => $story_tab));
    }

    /**
     * @Route("/story/{story_id}/detail", name="detail_story")
     */
    public function detailStoryAction(Request $request, $story_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Story $story */
        $story = $em->getRepository('StoryTellBundle:Story')->find($story_id);
        if (!$story) {
            throw $this->createNotFoundException();
        }

        $story_genres = $story->getGenres();

        $nb_chapters = $em->getRepository('StoryTellBundle:Story')->getNbChapters($story, true);

        $nb_pages = $em->getRepository('StoryTellBundle:Story')->getNbPages($story, true);

        return $this->render('StoryTellBundle:Default:detail_story.html.twig', array('story' => $story, 'story_genres' => $story_genres, 'nb_chapters' => $nb_chapters, 'nb_pages' => $nb_pages));
    }

    /**
     * @Route("/story/{story_id}/read", name="read_story")
     */
    public function readStoryAction(Request $request, $story_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Story $story */
        $story = $em->getRepository('StoryTellBundle:Story')->find($story_id);
        if (!$story) {
            throw $this->createNotFoundException();
        }

        /** @var Readings $reading */
        $reading = $em->getRepository('StoryTellBundle:Readings')->findOneBy(array('story' => $story, 'user' => $user));
        if (!$reading) {
            throw $this->createNotFoundException();
        }

        if ($reading->getIsFinished()) {
            $next_chapter = $em->getRepository('StoryTellBundle:Story')->getNextChapter($story, $reading->getStoryChapter(), true);
            if ($next_chapter) {
                $reading->setIsFinished(false);
                $em->persist($reading);
                $em->flush();
                return $this->redirectToRoute('read_next_page', array('reading_id' => $reading->getId()));
            }
            return $this->render('StoryTellBundle:Default:finished_story.html.twig', array('reading' => $reading));
        }

        $nb_chapter = $em->getRepository('StoryTellBundle:Story')->getNbChapters($story, true);
        $nb_pages_chapter = $em->getRepository('StoryTellBundle:StoryChapter')->getNumberLastPage($reading->getStoryChapter());

        return $this->render('StoryTellBundle:Default:read_story.html.twig', array('story' => $story, 'reading' => $reading, 'nb_chapters' => $nb_chapter, 'nb_pages_chapter' => $nb_pages_chapter));
    }

    /**
     * @Route("/reading/{reading_id}/previous_page", name="read_previous_page")
     */
    public function readPreviousPageAction(Request $request, $reading_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Readings $reading */
        $reading = $em->getRepository('StoryTellBundle:Readings')->find($reading_id);
        if (!$reading) {
            throw $this->createNotFoundException();
        }
        if ($reading->getUser() != $user) {
            throw $this->createAccessDeniedException();
        }

        if ($reading->getIsFinished()) {
            $reading->setIsFinished(false);
            $em->persist($reading);
            $em->flush();
        }
        else {
            $previous_page = $em->getRepository('StoryTellBundle:StoryChapter')->getPreviousPage($reading->getStoryChapter(), $reading->getStoryContent());
            if (!$previous_page) { // Change chapter
                $previous_chapter = $em->getRepository('StoryTellBundle:Story')->getPreviousChapter($reading->getStory(), $reading->getStoryChapter());
                if (!$previous_chapter) { // Story beginning
                    return $this->redirectToRoute('read_story', array('story_id' => $reading->getStory()->getId()));
                }
                $nb_page_chapter = $em->getRepository('StoryTellBundle:StoryChapter')->getNumberLastPage($previous_chapter);
                $previous_page = $em->getRepository('StoryTellBundle:StoryContent')->findOneBy(array('storyChapter' => $previous_chapter, 'page' => $nb_page_chapter));
                $reading->setStoryChapter($previous_chapter);
            }
            $reading->setStoryContent($previous_page);
            $em->persist($reading);
            $em->flush();
        }

        return $this->redirectToRoute('read_story', array('story_id' => $reading->getStory()->getId()));
    }

    /**
     * @Route("/reading/{reading_id}/next_page", name="read_next_page")
     */
    public function readNextPageAction(Request $request, $reading_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Readings $reading */
        $reading = $em->getRepository('StoryTellBundle:Readings')->find($reading_id);
        if (!$reading) {
            throw $this->createNotFoundException();
        }
        if ($reading->getUser() != $user) {
            throw $this->createAccessDeniedException();
        }

        $next_page = $em->getRepository('StoryTellBundle:StoryChapter')->getNextPage($reading->getStoryChapter(), $reading->getStoryContent());
        if (!$next_page) { // Change chapter
            $next_chapter = $em->getRepository('StoryTellBundle:Story')->getNextChapter($reading->getStory(), $reading->getStoryChapter(), true);
            if (!$next_chapter) { // Story finished
                $reading->setIsFinished(true);
                $em->persist($reading);
                $em->flush();
                return $this->redirectToRoute('read_story', array('story_id' => $reading->getStory()->getId()));
            }
            $next_page = $em->getRepository('StoryTellBundle:StoryContent')->findOneBy(array('storyChapter' => $next_chapter, 'page' => 1));
            $reading->setStoryChapter($next_chapter);
        }
        $reading->setStoryContent($next_page);
        $em->persist($reading);
        $em->flush();

        return $this->redirectToRoute('read_story', array('story_id' => $reading->getStory()->getId()));
    }

    /**
     * @Route("/reading/{reading_id}/read_again", name="read_again")
     */
    public function readAgainAction(Request $request, $reading_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Readings $reading */
        $reading = $em->getRepository('StoryTellBundle:Readings')->find($reading_id);
        if (!$reading) {
            throw $this->createNotFoundException();
        }
        if ($reading->getUser() != $user) {
            throw $this->createAccessDeniedException();
        }

        $reading->setStoryChapter($em->getRepository('StoryTellBundle:StoryChapter')->getFirstChapter($reading->getStory()));
        $reading->setStoryContent($em->getRepository('StoryTellBundle:StoryContent')->getFirstContent($reading->getStoryChapter()));
        $reading->setIsFinished(false);
        $em->persist($reading);
        $em->flush();

        return $this->redirectToRoute('read_story', array('story_id' => $reading->getStory()->getId()));
    }

    public function controlUrl($name, $obj)
    {
        $regex = '#(https?|ftp|ssh|mailto):\/\/[a-z0-9\/:%_+.,\#?!@&=-]+#i'; // '(https?:\/\/[^\s]+)'
        if (preg_match($regex, $obj->{'get'.ucfirst($name)}())) {
            $obj->{'set'.ucfirst($name)}(preg_replace($regex, ' ', $obj->{'get'.ucfirst($name)}()));
            $this->addFlash($name, "Urls are not allowed.");
        }
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
