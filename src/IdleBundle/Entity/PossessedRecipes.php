<?php

namespace IdleBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * PossessedRecipes
 *
 * @ORM\Table(name="possessed_recipes")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\PossessedRecipesRepository")
 */
class PossessedRecipes
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="Recipe")
     * @ORM\JoinColumn(name="recipe_id", referencedColumnName="id")
     */
    private $recipe;


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
     * Set user
     *
     * @param User $user
     * @return PossessedRecipes
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set recipe
     *
     * @param Recipe $recipe
     * @return PossessedRecipes
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
}
