<?php

namespace StoryTellBundle\Repository;

use Doctrine\ORM\EntityRepository;
use StoryTellBundle\Entity\StoryChapter;

/**
 * StoryContentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StoryContentRepository extends EntityRepository
{
    function getFirstContent(StoryChapter $storyChapter)
    {
        return $this->createQueryBuilder('sco')
            ->innerJoin('StoryTellBundle:StoryChapter', 'sch', 'WITH', 'sco.storyChapter = sch.id')
            ->where('sco.storyChapter = :storyChapter')
            ->setParameter('storyChapter', $storyChapter)
            ->andWhere('sco.page = 1')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
