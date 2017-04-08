<?php

namespace IdleBundle\Services;


use Doctrine\ORM\EntityManager;
use IdleBundle\Entity\Hero;
use IdleBundle\Entity\Target;

class BattleManager
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function applyOneLine($histo, Hero $hero, Target $target)
    {
        if ($histo['type'] == "HIT_E") {
            $target->setCurrentHealth($target->getCurrentHealth() - $histo['damage']);
        } else if ($histo['type'] == "HIT_H") {
            $hero->setCurrentHealth($hero->getCurrentHealth() - $histo['damage']);
        } else if ($histo['type'] == "GEN") {

        }
    }
}