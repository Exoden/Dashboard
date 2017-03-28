<?php

namespace ClickerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BattleHistory
 *
 * @ORM\Table(name="battle_history")
 * @ORM\Entity(repositoryClass="ClickerBundle\Repository\BattleHistoryRepository")
 */
class BattleHistory
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="ClickerBundle\Entity\Hero")
     * @ORM\JoinColumn(name="hero_id", referencedColumnName="id")
     */
    private $hero;

    /**
     * @var string
     *
     * @ORM\Column(name="historic", type="string", length=100000)
     */
    private $historic;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set hero
     *
     * @param Hero $hero
     * @return BattleHistory
     */
    public function setHero($hero)
    {
        $this->hero = $hero;

        return $this;
    }

    /**
     * Get hero
     *
     * @return Hero
     */
    public function getHero()
    {
        return $this->hero;
    }

    /**
     * Set historic
     *
     * @param string $historic
     * @return BattleHistory
     */
    public function setHistoric($historic)
    {
        $this->historic = $historic;

        return $this;
    }

    /**
     * Get historic
     *
     * @return string 
     */
    public function getHistoric()
    {
        return $this->historic;
    }
}
