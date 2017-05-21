<?php

namespace IdleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Food
 *
 * @ORM\Table(name="food")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\FoodRepository")
 */
class Food
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
     * @ORM\OneToOne(targetEntity="Item", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    private $item;

    /**
     * @var int
     *
     * @ORM\Column(name="health_regen", type="integer")
     */
    private $healthRegen;


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
     * Set item
     *
     * @param Item $item
     * @return Food
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
     * Set healthRegen
     *
     * @param integer $healthRegen
     * @return Food
     */
    public function setHealthRegen($healthRegen)
    {
        $this->healthRegen = $healthRegen;

        return $this;
    }

    /**
     * Get healthRegen
     *
     * @return integer 
     */
    public function getHealthRegen()
    {
        return $this->healthRegen;
    }
}
