<?php

namespace PickOneBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * QuestionGenreRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class QuestionGenreRepository extends EntityRepository
{
    function getAllGenresSortedBy($sort)
    {
        return $this->createQueryBuilder('qg')
            ->orderBy('qg.name', $sort)
            ->getQuery()
            ->getResult();
    }
}