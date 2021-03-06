<?php

namespace IdleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Enemy
 *
 * @ORM\Table(name="enemy")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\EnemyRepository")
 */
class Enemy
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
     * @ORM\OneToOne(targetEntity="Characteristics", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="characteristics_id", referencedColumnName="id")
     */
    private $characteristics;

    /**
     * @ORM\ManyToOne(targetEntity="Area", inversedBy="enemies")
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id")
     */
    private $area;

    /**
     * @var int
     *
     * @ORM\Column(name="min_field_level", type="integer")
     */
    private $minFieldLevel;

    /**
     * @var int
     *
     * @ORM\Column(name="max_field_level", type="integer")
     */
    private $maxFieldLevel;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="Loot", mappedBy="enemy", cascade={"persist", "remove"})
     */
    private $loots;


    public function __construct()
    {
        $this->loots = new ArrayCollection();
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
     * @return Enemy
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

    /**
     * Set characteristics
     *
     * @param Characteristics $characteristics
     * @return Enemy
     */
    public function setCharacteristics($characteristics)
    {
        $this->characteristics = $characteristics;

        return $this;
    }

    /**
     * Get characteristics
     *
     * @return Characteristics
     */
    public function getCharacteristics()
    {
        return $this->characteristics;
    }

    /**
     * Set area
     *
     * @param Area $area
     * @return Enemy
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set minFieldLevel
     *
     * @param integer $minFieldLevel
     * @return Enemy
     */
    public function setMinFieldLevel($minFieldLevel)
    {
        $this->minFieldLevel = $minFieldLevel;

        return $this;
    }

    /**
     * Get minFieldLevel
     *
     * @return integer
     */
    public function getMinFieldLevel()
    {
        return $this->minFieldLevel;
    }

    /**
     * Set maxFieldLevel
     *
     * @param integer $maxFieldLevel
     * @return Enemy
     */
    public function setMaxFieldLevel($maxFieldLevel)
    {
        $this->maxFieldLevel = $maxFieldLevel;

        return $this;
    }

    /**
     * Get maxFieldLevel
     *
     * @return integer
     */
    public function getMaxFieldLevel()
    {
        return $this->maxFieldLevel;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Enemy
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }
    
    
    public function addLoot(Loot $l)
    {
        if (!$this->loots->contains($l)) {
            $this->loots[] = $l;

            $l->setEnemy($this);
        }
        return $this;
    }

    public function removeLoot(Loot $loot)
    {
        $this->loots->removeElement($loot);
    }

    public function getLoots()
    {
        return $this->loots;
    }
}
