<?php

namespace IdleBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PossessedRecipesRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PossessedRecipesRepository extends EntityRepository
{
    public function getRecipesWithItemInArray($item)
    {
        return $this->createQueryBuilder('pr')
            ->innerJoin('pr.recipe', 'r')
            ->innerJoin('r.crafts', 'c')
            ->where(':item = c.itemNeeded')
            ->setParameter('item', $item)
            ->getQuery()
            ->getArrayResult();
    }

    public function getRecipesWithItem($item)
    {
        return $this->createQueryBuilder('pr')
            ->innerJoin('pr.recipe', 'r')
            ->innerJoin('r.crafts', 'c')
            ->where(':item = c.itemNeeded')
            ->setParameter('item', $item)
            ->getQuery()
            ->getResult();
    }
}