<?php

namespace StoryTellBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use StoryTellBundle\Entity\Story;
use StoryTellBundle\Entity\StoryChapter;
use StoryTellBundle\Entity\StoryGenre;

/**
 * StoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StoryRepository extends EntityRepository
{
    function getNbChapters(Story $story, $isPublished = false)
    {
        $q = $this->createQueryBuilder('s')
            ->select('count(sch) as nb_chapters')
            ->innerJoin('StoryTellBundle:StoryChapter', 'sch', 'WITH', 'sch.story = s.id')
            ->where('s.id = :story')
            ->setParameter('story', $story);
        if ($isPublished)
            $q = $q->andWhere('sch.isPublished = 1');
        $q = $q->getQuery()
            ->getSingleScalarResult();
        return $q;
    }

    function getNbPages(Story $story, $isPublished = false)
    {
        $q = $this->createQueryBuilder('s')
            ->select('count(sco) as nb_pages')
            ->innerJoin('StoryTellBundle:StoryChapter', 'sch', 'WITH', 'sch.story = s.id')
            ->innerJoin('StoryTellBundle:StoryContent', 'sco', 'WITH', 'sco.storyChapter = sch.id')
            ->where('s.id = :story')
            ->setParameter('story', $story);
        if ($isPublished)
            $q = $q->andWhere('sch.isPublished = 1');
        $q = $q->getQuery()
            ->getSingleScalarResult();
        return $q;
    }

    function getPreviousChapter(Story $story, StoryChapter $chapter)
    {
        $q =  $this->createQueryBuilder('s')
            ->select('sc')
            ->innerJoin('StoryTellBundle:StoryChapter', 'sc', 'WITH', 'sc.story = s.id')
            ->where('sc.story = :story')
            ->setParameter('story', $story)
            ->andWhere('sc.chapter = :previous_chapter')
            ->setParameter('previous_chapter', $chapter->getChapter() - 1)
            ->getQuery()
            ->getOneOrNullResult();
        return $q;
    }

    function getNextChapter(Story $story, StoryChapter $chapter, $isPublished = false)
    {
        $q =  $this->createQueryBuilder('s')
            ->select('sc')
            ->innerJoin('StoryTellBundle:StoryChapter', 'sc', 'WITH', 'sc.story = s.id')
            ->where('sc.story = :story')
            ->setParameter('story', $story)
            ->andWhere('sc.chapter = :next_chapter')
            ->setParameter('next_chapter', $chapter->getChapter() + 1);
        if ($isPublished)
            $q = $q->andWhere('sc.isPublished = 1');
        $q = $q->getQuery()
            ->getOneOrNullResult();
        return $q;
    }

    function getStoriesWithGenre(StoryGenre $genre)
    {
        return $this->createQueryBuilder('s')
            ->where(':genre MEMBER OF s.genres')
            ->setParameter('genre', $genre)
            ->andWhere('s.isPublished = 1')
            ->getQuery()
            ->getResult();
    }
}
