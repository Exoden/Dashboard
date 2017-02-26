<?php

namespace StoryTellBundle\Controller;

use StoryTellBundle\Entity\Readings;
use StoryTellBundle\Entity\Story;
use StoryTellBundle\Entity\StoryChapter;
use StoryTellBundle\Entity\StoryContent;
use StoryTellBundle\Form\StoryChapterType;
use StoryTellBundle\Form\StoryContentType;
use StoryTellBundle\Form\StoryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $story = new Story();
        $form = $this->createForm(StoryType::class, $story);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $story->setAuthor($user);
            $em->persist($story);
            $em->flush();
            $this->addFlash('success', "New Story saved");
            return $this->redirectToRoute('edit_story', array('story_id' => $story->getId(), 'chapters' => null));
        }
        return $this->render('StoryTellBundle:Default:create_story.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/story/{story_id}/edit", name="edit_story")
     */
    public function editStoryAction(Request $request, $story_id)
    {
        $em = $this->getDoctrine()->getManager();

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

        $form = $this->createForm(StoryType::class, $story);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($story);
            $em->flush();
            $this->addFlash('success', "Changes saved");
        }

        return $this->render('StoryTellBundle:Default:edit_story.html.twig', array('story' => $story, 'chapters' => $chapters, 'form' => $form->createView()));
    }

    /**
     * @Route("/story/{story_id}/chapter/create", name="create_chapter")
     */
    public function createChapterAction(Request $request, $story_id)
    {
        $em = $this->getDoctrine()->getManager();

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
        $form = $this->createForm(StoryChapterType::class, $chapter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $chapter->setStory($story);
            $chapter->setChapter($em->getRepository('StoryTellBundle:Story')->getNbChapters($story) + 1);
            $em->persist($chapter);
            $em->flush();
            $this->addFlash('success', "New Chapter saved");
            return $this->redirectToRoute('edit_chapter', array('story_id' => $story->getId(), 'chapter_id' => $chapter->getId(), 'contents' => null));
        }
        return $this->render('StoryTellBundle:Default:create_chapter.html.twig', array('story' => $story, 'form' => $form->createView()));
    }

    /**
     * @Route("/story/{story_id}/chapter/{chapter_id}/edit", name="edit_chapter")
     */
    public function editChapterAction(Request $request, $story_id, $chapter_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Story $story */
        $story = $em->getRepository('StoryTellBundle:Story')->find($story_id);
        if (!$story) {
            throw $this->createNotFoundException();
        }
        if ($story->getAuthor() != $user) {
            throw $this->createAccessDeniedException();
        }

        $chapter = $em->getRepository('StoryTellBundle:StoryChapter')->find($chapter_id);
        if (!$chapter) {
            throw $this->createNotFoundException();
        }

        $contents = $em->getRepository('StoryTellBundle:StoryContent')->findBy(array('storyChapter' => $chapter));

        $form = $this->createForm(StoryChapterType::class, $chapter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($chapter);
            $em->flush();
            $this->addFlash('success', "Changes saved");
        }

        return $this->render('StoryTellBundle:Default:edit_chapter.html.twig', array('story' => $story, 'chapter' => $chapter, 'contents' => $contents, 'form' => $form->createView()));
    }

    /**
     * @Route("/story/{story_id}/chapter/{chapter_id}/content/create", name="create_content")
     */
    public function createContentAction(Request $request, $story_id, $chapter_id)
    {
        $em = $this->getDoctrine()->getManager();

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
        if ($form->isSubmitted() && $form->isValid()) {
            $content->setStoryChapter($chapter);
            $content->setPage($em->getRepository('StoryTellBundle:StoryChapter')->getNumberLastPage($chapter) + 1);
            $em->persist($content);
            $em->flush();
            $this->addFlash('success', "New Content saved");
            return $this->redirectToRoute('edit_content', array('story_id' => $story->getId(), 'chapter_id' => $chapter->getId(), 'content_id' => $content->getId()));
        }
        return $this->render('StoryTellBundle:Default:create_content.html.twig', array('story' => $story, 'chapter' => $chapter, 'form' => $form->createView()));
    }

    /**
     * @Route("/story/{story_id}/chapter/{chapter_id}/content/{content_id}/edit", name="edit_content")
     */
    public function editContentAction(Request $request, $story_id, $chapter_id, $content_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        /** @var Story $story */
        $story = $em->getRepository('StoryTellBundle:Story')->find($story_id);
        if (!$story) {
            throw $this->createNotFoundException();
        }
        if ($story->getAuthor() != $user) {
            throw $this->createAccessDeniedException();
        }

        $chapter = $em->getRepository('StoryTellBundle:StoryChapter')->find($chapter_id);
        if (!$chapter) {
            throw $this->createNotFoundException();
        }

        $content = $em->getRepository('StoryTellBundle:StoryContent')->find($content_id);
        if (!$content) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(StoryContentType::class, $content);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // TODO : Comprendre pourquoi je ne recois pas la tabulation en début de chaine ($form['content']->getData())
            $em->persist($content);
            $em->flush();
            $this->addFlash('success', "Changes saved");
        }

        return $this->render('StoryTellBundle:Default:edit_content.html.twig', array('story' => $story, 'chapter' => $chapter, 'content' => $content, 'form' => $form->createView()));
    }

    /**
     * @Route("/stories", name="stories")
     */
    public function storiesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Story $story */
        $stories = $em->getRepository('StoryTellBundle:Story')->findAll();

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

        /** @var Story $story */
        $stories = $em->getRepository('StoryTellBundle:Story')->getStoriesWithGenre($genre_selected);

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
     * @Route("/my_readings", name="my_readings")
     */
    public function myReadingsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $readings = $em->getRepository('StoryTellBundle:Story')->getReadingsOfUser($user);

        return $this->render('StoryTellBundle:Default:my_readings.html.twig', array('readings' => $readings));
    }

    /**
     * @Route("/my_stories", name="my_stories")
     */
    public function myStoriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $stories = $em->getRepository('StoryTellBundle:Story')->findBy(array('author' => $user));

        return $this->render('StoryTellBundle:Default:my_stories.html.twig', array('stories' => $stories));
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

        $nb_chapters = $em->getRepository('StoryTellBundle:Story')->getNbChapters($story);

        $nb_pages = $em->getRepository('StoryTellBundle:Story')->getNbPages($story);

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
            return $this->render('StoryTellBundle:Default:finished_story.html.twig', array('reading' => $reading));
        }

        $nb_pages_chapter = $em->getRepository('StoryTellBundle:StoryChapter')->getNumberLastPage($reading->getStoryChapter());

        return $this->render('StoryTellBundle:Default:read_story.html.twig', array('story' => $story, 'reading' => $reading, 'nb_pages_chapter' => $nb_pages_chapter));
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
            $next_chapter = $em->getRepository('StoryTellBundle:Story')->getNextChapter($reading->getStory(), $reading->getStoryChapter());
            if (!$next_chapter) { // Story finished
                $reading->setIsFinished(true);
                $em->persist($reading);
                $em->flush();
                return $this->redirectToRoute('read_story', array('story_id' => $reading->getStory()->getId()));
            }
            $next_page = $em->getRepository('StoryTellBundle:StoryContent')->findOneBy(array('storyChapter' => $next_chapter));
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
}
