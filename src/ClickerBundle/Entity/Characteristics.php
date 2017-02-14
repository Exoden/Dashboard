<?php

namespace ClickerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Characteristics
 *
 * @ORM\Table(name="characteristics")
 * @ORM\Entity(repositoryClass="ClickerBundle\Repository\CharacteristicsRepository")
 */
class Characteristics
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
     * @var int
     *
     * @ORM\Column(name="damage_minimum", type="integer")
     */
    private $damageMinimum;

    /**
     * @var int
     *
     * @ORM\Column(name="damage_maximum", type="integer")
     */
    private $damageMaximum;

    /**
     * @var float
     *
     * @ORM\Column(name="attack_delay", type="float")
     */
    private $attackDelay;

    /**
     * @var int
     *
     * @ORM\Column(name="hit_precision", type="integer")
     */
    private $hitPrecision;

    /**
     * @var int
     *
     * @ORM\Column(name="health", type="integer")
     */
    private $health;

    /**
     * @var int
     *
     * @ORM\Column(name="armor", type="integer")
     */
    private $armor;

    /**
     * @var int
     *
     * @ORM\Column(name="dodge", type="integer")
     */
    private $dodge;

    /**
     * @var int
     *
     * @ORM\Column(name="speed", type="integer")
     */
    private $speed;


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
     * @return Characteristics
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
     * Set damageMinimum
     *
     * @param integer $damageMinimum
     * @return Characteristics
     */
    public function setDamageMinimum($damageMinimum)
    {
        $this->damageMinimum = $damageMinimum;

        return $this;
    }

    /**
     * Get damageMinimum
     *
     * @return integer 
     */
    public function getDamageMinimum()
    {
        return $this->damageMinimum;
    }

    /**
     * Set damageMaximum
     *
     * @param integer $damageMaximum
     * @return Characteristics
     */
    public function setDamageMaximum($damageMaximum)
    {
        $this->damageMaximum = $damageMaximum;

        return $this;
    }

    /**
     * Get damageMaximum
     *
     * @return integer 
     */
    public function getDamageMaximum()
    {
        return $this->damageMaximum;
    }

    /**
     * Set attackDelay
     *
     * @param float $attackDelay
     * @return Characteristics
     */
    public function setAttackDelay($attackDelay)
    {
        $this->attackDelay = $attackDelay;

        return $this;
    }

    /**
     * Get attackDelay
     *
     * @return float 
     */
    public function getAttackDelay()
    {
        return $this->attackDelay;
    }

    /**
     * Set hitPrecision
     *
     * @param integer $hitPrecision
     * @return Characteristics
     */
    public function setHitPrecision($hitPrecision)
    {
        $this->hitPrecision = $hitPrecision;

        return $this;
    }

    /**
     * Get hitPrecision
     *
     * @return integer 
     */
    public function getHitPrecision()
    {
        return $this->hitPrecision;
    }

    /**
     * Set health
     *
     * @param integer $health
     * @return Characteristics
     */
    public function setHealth($health)
    {
        $this->health = $health;

        return $this;
    }

    /**
     * Get health
     *
     * @return integer 
     */
    public function getHealth()
    {
        return $this->health;
    }

    /**
     * Set armor
     *
     * @param integer $armor
     * @return Characteristics
     */
    public function setArmor($armor)
    {
        $this->armor = $armor;

        return $this;
    }

    /**
     * Get armor
     *
     * @return integer 
     */
    public function getArmor()
    {
        return $this->armor;
    }

    /**
     * Set dodge
     *
     * @param integer $dodge
     * @return Characteristics
     */
    public function setDodge($dodge)
    {
        $this->dodge = $dodge;

        return $this;
    }

    /**
     * Get dodge
     *
     * @return integer 
     */
    public function getDodge()
    {
        return $this->dodge;
    }

    /**
     * Set speed
     *
     * @param integer $speed
     * @return Characteristics
     */
    public function setSpeed($speed)
    {
        $this->speed = $speed;

        return $this;
    }

    /**
     * Get speed
     *
     * @return integer 
     */
    public function getSpeed()
    {
        return $this->speed;
    }
}
