<?php

namespace IdleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Recipe
 *
 * @ORM\Table(name="recipe")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\RecipeRepository")
 */
class Recipe
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
     * @ORM\OneToOne(targetEntity="Item")
     * @ORM\JoinColumn(name="item_created_id", referencedColumnName="id")
     */
    private $itemCreated;

    /**
     * @ORM\OneToOne(targetEntity="Item", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    private $item;

    /**
     * @ORM\OneToMany(targetEntity="Craft", mappedBy="recipe", cascade={"persist", "remove"})
     */
    private $crafts;


    public function __construct()
    {
        $this->crafts = new ArrayCollection();
    }


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
     * Set itemCreated
     *
     * @param Item $itemCreated
     * @return Recipe
     */
    public function setItemCreated($itemCreated)
    {
        $this->itemCreated = $itemCreated;

        return $this;
    }

    /**
     * Get itemCreated
     *
     * @return Item
     */
    public function getItemCreated()
    {
        return $this->itemCreated;
    }

    /**
     * Set item
     *
     * @param Item $item
     * @return Recipe
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


    public function addCraft(Craft $craft)
    {
        if (!$this->crafts->contains($craft)) {
            $this->crafts[] = $craft;

            $craft->setRecipe($this);
        }
        return $this;
    }

    public function removeCraft(Craft $craft)
    {
        $this->crafts->removeElement($craft);
    }

    public function getCrafts()
    {
        return $this->crafts;
    }
}
