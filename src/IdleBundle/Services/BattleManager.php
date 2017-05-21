<?php

namespace IdleBundle\Services;


use Doctrine\ORM\EntityManager;
use IdleBundle\Entity\Enemy;
use IdleBundle\Entity\Hero;
use IdleBundle\Entity\Inventory;
use IdleBundle\Entity\Item;
use IdleBundle\Entity\Loot;
use Symfony\Component\DependencyInjection\Container;

class BattleManager
{
    private $em;
    private $container;

    public function __construct(EntityManager $em, Container $container)
    {
        $this->em = $em;
        $this->container = $container;
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
            $this->em->flush();

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

    public function playAverageBattleFrom($now) // TODO !!!
    {
        $now = microtime(true);

        return $now;
    }

    public function generateEnemy(Hero $hero)
    {
        /** @var Enemy $enemy */
        $enemies = $this->em->getRepository('IdleBundle:Enemy')->getFromAreaAndFieldLevel($hero->getActivatedZone()->getArea(), $hero->getActivatedZone()->getCurrentField());

        $enemy = $enemies[rand(0, count($enemies) - 1)];
        // TODO : If no results random all enemies from Area

        return $enemy;
    }

    public function createGenAction(Hero $hero, $time, $dead_enemy_id = 0, $with_loot = false)
    {
        /** @var Enemy $new_enemy */
        $new_enemy = $this->generateEnemy($hero); // TODO : Randomise in the service

        if ($with_loot) {
            /** @var Enemy $old_enemy */
            $old_enemy = $this->em->getRepository('IdleBundle:Enemy')->find($dead_enemy_id);

            $flash_msg = "";
            $loots = $old_enemy->getLoots();
            $arr_loot = array();
            /** @var Loot $loot */
            foreach ($loots as $loot) {
                $rate = rand(1, 100000); // Precision 0.001
                if ($rate < ($loot->getPercent() * 1000)) {
                    array_push($arr_loot, $loot->getItem()->getId());

                    $flash_msg .= "+1 " . $loot->getItem()->getName() . "\n";
                }
            }

            $res = array(
                'type' => "GEN",
                'time' => $time,
                'enemy' => $new_enemy->getId(),
                'name' => $new_enemy->getName(),
                'loot_msg' => (($flash_msg != "") ? $flash_msg : "No loots"),
                'loots' => $arr_loot,
                'image' => $this->container->get('templating.helper.assets')->getUrl('images/Idle/Enemy/' . $new_enemy->getImage()),
                'currentHealth' => $new_enemy->getCharacteristics()->getHealth(),
                'health' => $new_enemy->getCharacteristics()->getHealth(),
                'stats' => $this->em->getRepository('IdleBundle:Characteristics')->getStatsInArray($new_enemy->getCharacteristics()));
        }
        else {
            $res = array(
                'type' => "GEN",
                'time' => $time,
                'enemy' => $new_enemy->getId(),
                'name' => $new_enemy->getName(),
                'image' => $this->container->get('templating.helper.assets')->getUrl('images/Idle/Enemy/' . $new_enemy->getImage()),
                'currentHealth' => $new_enemy->getCharacteristics()->getHealth(),
                'health' => $new_enemy->getCharacteristics()->getHealth(),
                'stats' => $this->em->getRepository('IdleBundle:Characteristics')->getStatsInArray($new_enemy->getCharacteristics()));
        }

        return $res;
    }
}