<?php

namespace IdleBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\OneToMany(targetEntity="Zone", mappedBy="hero")
     */
    private $zones;

    /**
     * @ORM\OneToMany(targetEntity="FoodStack", mappedBy="hero")
     */
    private $foodStackList;

    /**
     * @ORM\ManyToMany(targetEntity="IdleBundle\Entity\Stuff")
     * @ORM\JoinTable(name="link_hero_stuff",
     *      joinColumns={@ORM\JoinColumn(name="hero_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="stuff_id", referencedColumnName="id")}
     *      )
     */
    private $stuffs;


    public function __construct() {
        $this->stuffs = new ArrayCollection();
        $this->foodStackList = new ArrayCollection();
        $this->zones = new ArrayCollection();
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
    
    
    public function addStuff(Stuff $stuff)
    {
        $this->stuffs[] = $stuff;

        return $this;
    }

    public function removeStuff(Stuff $stuff)
    {
        $this->stuffs->removeElement($stuff);
    }

    public function getStuffs()
    {
        return $this->stuffs;
    }

    public function getTypeStuff($type)
    {
        /** @var Stuff $stuff */
        foreach ($this->stuffs as $stuff) {
            if ($stuff->getType() == $type)
            return $stuff;
        }
        return null;
    }


    public function addfoodStackList(foodStack $foodStack)
    {
        if (!$this->foodStackList->contains($foodStack)) {
            $this->foodStackList[] = $foodStack;

            $foodStack->setHero($this);
        }
        return $this;
    }

    public function removefoodStackList(foodStack $foodStack)
    {
        $this->foodStackList->removeElement($foodStack);
    }

    public function getfoodStackList()
    {
        return $this->foodStackList;
    }


    public function addZone(Zone $zone)
    {
        if (!$this->zones->contains($zone)) {
            $this->zones[] = $zone;

            $zone->setHero($this);
        }
        return $this;
    }

    public function removeZone(Zone $zone)
    {
        $this->zones->removeElement($zone);
    }

    public function getZones()
    {
        return $this->zones;
    }

    public function getActivatedZone()
    {
        /** @var Zone $zone */
        foreach ($this->zones as $zone) {
            if ($zone->getActivated() == true)
                return $zone;
        }

        return null;
    }
}
