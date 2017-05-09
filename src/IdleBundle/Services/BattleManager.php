<?php

namespace IdleBundle\Services;


use Doctrine\ORM\EntityManager;
use IdleBundle\Entity\Enemy;
use IdleBundle\Entity\Hero;
use IdleBundle\Entity\Inventory;
use IdleBundle\Entity\Item;
use IdleBundle\Entity\Loot;
use IdleBundle\Entity\Target;

class BattleManager
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function cumulStatsHero($hero_id)
    {
        /** @var Hero $hero */
        $hero = $this->em->getRepository('IdleBundle:Hero')->find($hero_id);

        // TODO : Cumul stats + skill tree
    }

    public function playOneAction($histo, Hero $hero)
    {
        if ($histo['type'] == "HIT_E") {
            $hero->getTarget()->setCurrentHealth($hero->getTarget()->getCurrentHealth() - $histo['damage']);
        } else if ($histo['type'] == "HIT_H") {
            $hero->setCurrentHealth($hero->getCurrentHealth() - $histo['damage']);
        } else if ($histo['type'] == "GEN") {
            /** @var Enemy $enemy */
            $enemy = $this->em->getRepository('IdleBundle:Enemy')->find($histo['enemy']);
            $hero->getTarget()->setEnemy($enemy);
            $hero->getTarget()->setCurrentHealth($enemy->getCharacteristics()->getHealth());

            // Get the loots
            if (isset($histo['loots'])) {
                /** @var Loot $loot */
                foreach ($histo['loots'] as $loot) {
                    /** @var Item $item */
                    $item = $this->em->getRepository('IdleBundle:Item')->find($loot);
                    /** @var Inventory $inv */
                    $inv = $this->em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $hero->getUser(), 'item' => $item));
                    if (!$inv) {
                        $inv = new Inventory();
                        $inv->setUser($hero->getUser());
                        $inv->setItem($item);
                        $inv->setQuantity(1);
                    } else
                        $inv->setQuantity($inv->getQuantity() + 1);
                    $this->em->persist($inv);
                }
            }
        }
        $this->em->flush();
    }

    public function playAverageBattleFrom($now)
    {
        $now = microtime(true);

        return $now;
    }
}