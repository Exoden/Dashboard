<?php

namespace IdleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Craft
 *
 * @ORM\Table(name="craft")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\CraftRepository")
 */
class Craft
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
     * @ORM\ManyToOne(targetEntity="Recipe", inversedBy="crafts")
     * @ORM\JoinColumn(name="recipe_id", referencedColumnName="id", nullable=false)
     */
    private $recipe;

    /**
     * @ORM\OneToOne(targetEntity="Item")
     * @ORM\JoinColumn(name="item_needed_id", referencedColumnName="id")
     */
    private $itemNeeded;

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
     * Set recipe
     *
     * @param Recipe $recipe
     * @return Craft
     */
    public function setRecipe($recipe)
    {
        $this->recipe = $recipe;

        return $this;
    }

    /**
     * Get recipe
     *
     * @return Recipe
     */
    public function getRecipe()
    {
        return $this->recipe;
    }

    /**
     * Set itemNeeded
     *
     * @param Item $itemNeeded
     * @return Craft
     */
    public function setItemNeeded($itemNeeded)
    {
        $this->itemNeeded = $itemNeeded;

        return $this;
    }

    /**
     * Get itemNeeded
     *
     * @return Item
     */
    public function getItemNeeded()
    {
        return $this->itemNeeded;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return Craft
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
