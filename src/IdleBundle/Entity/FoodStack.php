<?php

namespace IdleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FoodStack
 *
 * @ORM\Table(name="food_stack")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\FoodStackRepository")
 */
class FoodStack
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
     * @ORM\ManyToOne(targetEntity="Item")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    private $item;

    /**
     * @ORM\ManyToOne(targetEntity="Hero")
     * @ORM\JoinColumn(name="hero_id", referencedColumnName="id")
     */
    private $hero;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;


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
     * @return FoodStack
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
     * Set hero
     *
     * @param Hero $hero
     * @return FoodStack
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
     * Set quantity
     *
     * @param integer $quantity
     * @return FoodStack
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
