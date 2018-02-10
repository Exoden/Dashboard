<?php

namespace PickOneBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PickOneBundle\Entity\QuestionGenre;

/**
 * QuestionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class QuestionRepository extends EntityRepository
{
    function getQuestionsWithGenre(QuestionGenre $genre = null, $paginate = false)
    {
        $q = $this->createQueryBuilder('q');

        if ($genre != null)
            $q->where(':genre MEMBER OF s.genres')
                ->setParameter('genre', $genre);

        $q->getQuery();

        if ($paginate)
            return $q;

        return $q->getResult();
    }

    function getQuestionsFromAuthor($user, $paginate = false)
    {
        $q = $this->createQueryBuilder('q')
            ->where('q.author = :user')
            ->setParameter('user', $user)
            ->getQuery();

        if ($paginate)
            return $q;

        return $q->getResult();
    }
}