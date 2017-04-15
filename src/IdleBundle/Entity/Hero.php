<?php

namespace IdleBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Hero
 *
 * @ORM\Table(name="hero")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\HeroRepository")
 */
class Hero
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="age", type="integer")
     */
    private $age;

    /**
     * @var int
     *
     * @ORM\Column(name="currentHealth", type="integer")
     */
    private $currentHealth;

    /**
     * @ORM\OneToOne(targetEntity="Characteristics", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="characteristics_id", referencedColumnName="id")
     */
    private $characteristics;

    /**
     * @var int
     *
     * @ORM\Column(name="field_level", type="integer")
     */
    private $fieldLevel;

    /**
     * @var int
     *
     * @ORM\Column(name="field_max_level", type="integer")
     */
    private $fieldMaxLevel;

    /**
     * @ORM\OneToOne(targetEntity="Target", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="target_id", referencedColumnName="id")
     */
    private $target;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_rested", type="boolean")
     */
    private $isRested;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="rest_start_time", type="datetime", nullable=true)
     */
    private $restStartTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="rest_end_time", type="datetime", nullable=true)
     */
    private $restEndTime;


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
     * @return Hero
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
     * Set user
     *
     * @param User $user
     * @return Hero
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
     * Set age
     *
     * @param integer $age
     * @return Hero
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return integer
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set currentHealth
     *
     * @param integer $currentHealth
     * @return Hero
     */
    public function setCurrentHealth($currentHealth)
    {
        $this->currentHealth = $currentHealth;

        return $this;
    }

    /**
     * Get currentHealth
     *
     * @return integer
     */
    public function getCurrentHealth()
    {
        return $this->currentHealth;
    }

    /**
     * Set characteristics
     *
     * @param Characteristics $characteristics
     * @return Hero
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
     * Set fieldLevel
     *
     * @param integer $fieldLevel
     * @return Hero
     */
    public function setFieldLevel($fieldLevel)
    {
        $this->fieldLevel = $fieldLevel;

        return $this;
    }

    /**
     * Get fieldLevel
     *
     * @return integer 
     */
    public function getFieldLevel()
    {
        return $this->fieldLevel;
    }

    /**
     * Set fieldMaxLevel
     *
     * @param integer $fieldMaxLevel
     * @return Hero
     */
    public function setFieldMaxLevel($fieldMaxLevel)
    {
        $this->fieldMaxLevel = $fieldMaxLevel;

        return $this;
    }

    /**
     * Get fieldMaxLevel
     *
     * @return integer 
     */
    public function getFieldMaxLevel()
    {
        return $this->fieldMaxLevel;
    }

    /**
     * Set target
     *
     * @param Target $target
     * @return Hero
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return Target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set isRested
     *
     * @param boolean $isRested
     * @return Hero
     */
    public function setIsRested($isRested)
    {
        $this->isRested = $isRested;

        return $this;
    }

    /**
     * Get isRested
     *
     * @return boolean 
     */
    public function getIsRested()
    {
        return $this->isRested;
    }

    /**
     * Set restStartTime
     *
     * @param \DateTime $restStartTime
     * @return Hero
     */
    public function setRestStartTime($restStartTime)
    {
        $this->restStartTime = $restStartTime;

        return $this;
    }

    /**
     * Get restStartTime
     *
     * @return \DateTime 
     */
    public function getRestStartTime()
    {
        return $this->restStartTime;
    }

    /**
     * Set restEndTime
     *
     * @param \DateTime $restEndTime
     * @return Hero
     */
    public function setRestEndTime($restEndTime)
    {
        $this->restEndTime = $restEndTime;

        return $this;
    }

    /**
     * Get restEndTime
     *
     * @return \DateTime 
     */
    public function getRestEndTime()
    {
        return $this->restEndTime;
    }
}
