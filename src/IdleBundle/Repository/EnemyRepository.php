<?php

namespace IdleBundle\Repository;

use Doctrine\ORM\EntityRepository;
use IdleBundle\Entity\Area;

/**
 * EnemyRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EnemyRepository extends EntityRepository
{
    public function getFromAreaAndFieldLevel(Area $area, $field_level)
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.area', 'a')
            ->where('e.area = a.id')
            ->andWhere('a.id = :area')
            ->setParameter('area', $area->getId())
            ->andWhere('e.minFieldLevel <= ' . $field_level)
            ->andWhere('e.maxFieldLevel >= ' . $field_level)
            ->getQuery()
            ->getResult();
    }
}
