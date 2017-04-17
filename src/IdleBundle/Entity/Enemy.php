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
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="Loot", mappedBy="enemy", cascade={"persist", "remove"})
     */
    private $loot;


    public function __construct()
    {
        $this->loot = new ArrayCollection();
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
        if (!$this->loot->contains($l)) {
            $this->loot[] = $l;

            $l->setEnemy($this);
        }
        return $this;
    }

    public function removeLoot(Loot $loot)
    {
        $this->loot->removeElement($loot);
    }

    public function getLoots()
    {
        return $this->loot;
    }
}
