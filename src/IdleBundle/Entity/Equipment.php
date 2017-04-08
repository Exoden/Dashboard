<?php

namespace IdleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Equipment
 *
 * @ORM\Table(name="equipment")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\EquipmentRepository")
 */
class Equipment
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
     * @ORM\OneToOne(targetEntity="Hero")
     * @ORM\JoinColumn(name="hero_id", referencedColumnName="id")
     */
    private $hero;

    /**
     * @ORM\OneToOne(targetEntity="Stuff")
     * @ORM\JoinColumn(name="stuff_id", referencedColumnName="id")
     */
    private $stuff;


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
     * @return Equipment
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
     * Set stuff
     *
     * @param Stuff $stuff
     * @return Equipment
     */
    public function setStuff($stuff)
    {
        $this->stuff = $stuff;

        return $this;
    }

    /**
     * Get stuff
     *
     * @return Stuff
     */
    public function getStuff()
    {
        return $this->stuff;
    }
}
