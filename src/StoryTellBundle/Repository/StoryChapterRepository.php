<?php

namespace StoryTellBundle\Repository;

use Doctrine\ORM\EntityRepository;
use StoryTellBundle\Entity\Story;
use StoryTellBundle\Entity\StoryChapter;
use StoryTellBundle\Entity\StoryContent;

/**
 * StoryChapterRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StoryChapterRepository extends EntityRepository
{
    function getNumberLastPage(StoryChapter $chapter)
    {
        return $this->createQueryBuilder('sc')
            ->select('max(c.page) as value')
            ->innerJoin('StoryTellBundle:StoryContent', 'c', 'WITH', 'c.storyChapter = sc.id')
            ->where('c.storyChapter = :chapter')
            ->setParameter('chapter', $chapter)
            ->getQuery()
            ->getSingleScalarResult();
    }

    function getPreviousPage(StoryChapter $chapter, StoryContent $content)
    {
        return $this->createQueryBuilder('sc')
            ->select('c')
            ->innerJoin('StoryTellBundle:StoryContent', 'c', 'WITH', 'c.storyChapter = sc.id')
            ->where('c.storyChapter = :chapter')
            ->setParameter('chapter', $chapter)
            ->andWhere('c.page = :previous_page')
            ->setParameter('previous_page', $content->getPage() - 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    function getNextPage(StoryChapter $chapter, StoryContent $content)
    {
        return $this->createQueryBuilder('sc')
            ->select('c')
            ->innerJoin('StoryTellBundle:StoryContent', 'c', 'WITH', 'c.storyChapter = sc.id')
            ->where('c.storyChapter = :chapter')
            ->setParameter('chapter', $chapter)
            ->andWhere('c.page = :next_page')
            ->setParameter('next_page', $content->getPage() + 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    function getFirstChapter(Story $story)
    {
        return $this->createQueryBuilder('sc')
            ->innerJoin('StoryTellBundle:Story', 's', 'WITH', 'sc.story = s.id')
            ->where('sc.story = :story')
            ->setParameter('story', $story)
            ->andWhere('sc.chapter = 1')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
