<?php

namespace IdleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zone
 *
 * @ORM\Table(name="zone")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\ZoneRepository")
 */
class Zone
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
     * @ORM\ManyToOne(targetEntity="IdleBundle\Entity\Hero")
     * @ORM\JoinColumn(name="hero_id", referencedColumnName="id")
     */
    private $hero;

    /**
     * @ORM\ManyToOne(targetEntity="IdleBundle\Entity\Area")
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id")
     */
    private $area;

    /**
     * @var int
     *
     * @ORM\Column(name="current_field", type="integer")
     */
    private $currentField;

    /**
     * @var int
     *
     * @ORM\Column(name="max_field", type="integer")
     */
    private $maxField;

    /**
     * @var boolean
     *
     * @ORM\Column(name="activated", type="boolean")
     */
    private $activated;


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
     * @return Zone
     */
    public function setHero(Hero $hero)
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
     * Set area
     *
     * @param Area $area
     * @return Zone
     */
    public function setArea(Area $area)
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
     * Set currentField
     *
     * @param integer $currentField
     * @return Zone
     */
    public function setCurrentField($currentField)
    {
        $this->currentField = $currentField;

        return $this;
    }

    /**
     * Get currentField
     *
     * @return integer 
     */
    public function getCurrentField()
    {
        return $this->currentField;
    }

    /**
     * Set maxField
     *
     * @param integer $maxField
     * @return Zone
     */
    public function setMaxField($maxField)
    {
        $this->maxField = $maxField;

        return $this;
    }

    /**
     * Get maxField
     *
     * @return integer
     */
    public function getMaxField()
    {
        return $this->maxField;
    }

    /**
     * Set activated
     *
     * @param boolean $activated
     * @return Zone
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * Get activated
     *
     * @return boolean
     */
    public function getActivated()
    {
        return $this->activated;
    }
}
