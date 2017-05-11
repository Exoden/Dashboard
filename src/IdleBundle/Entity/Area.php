<?php

namespace IdleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Area
 *
 * @ORM\Table(name="area")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\AreaRepository")
 */
class Area
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Enemy", mappedBy="area")
     */
    private $enemies;


    public function __construct()
    {
        $this->enemies = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Area
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }


    public function addEnemy(Enemy $enemy)
    {
        if (!$this->enemies->contains($enemy)) {
            $this->enemies[] = $enemy;

            $enemy->setArea($this);
        }
        return $this;
    }

    public function removeEnemy(Enemy $enemy)
    {
        $this->enemies->removeElement($enemy);
    }

    public function getEnemy()
    {
        return $this->enemies;
    }
}
