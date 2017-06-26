<?php

namespace IdleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loot
 *
 * @ORM\Table(name="loot")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\LootRepository")
 */
class Loot
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
     * @ORM\ManyToOne(targetEntity="Enemy", inversedBy="loots")
     * @ORM\JoinColumn(name="enemy_id", referencedColumnName="id", nullable=false)
     */
    private $enemy;

    /**
     * @ORM\ManyToOne(targetEntity="Item")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false)
     */
    private $item;

    /**
     * @var int
     *
     * @ORM\Column(name="percent", type="float")
     */
    private $percent;


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
     * Set enemy
     *
     * @param Enemy $enemy
     * @return Loot
     */
    public function setEnemy($enemy)
    {
        $this->enemy = $enemy;

        return $this;
    }

    /**
     * Get enemy
     *
     * @return Enemy
     */
    public function getEnemy()
    {
        return $this->enemy;
    }

    /**
     * Set item
     *
     * @param Item $item
     * @return Loot
     */
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set percent
     *
     * @param integer $percent
     * @return Loot
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * Get percent
     *
     * @return integer 
     */
    public function getPercent()
    {
        return $this->percent;
    }
}
