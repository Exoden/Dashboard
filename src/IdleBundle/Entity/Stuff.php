<?php

namespace IdleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stuff
 *
 * @ORM\Table(name="stuff")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\StuffRepository")
 */
class Stuff
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
     * @ORM\OneToOne(targetEntity="Characteristics")
     * @ORM\JoinColumn(name="characteristics_id", referencedColumnName="id")
     */
    private $characteristics;

    /**
     * @ORM\ManyToOne(targetEntity="StuffType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=false)
     */
    private $type;

    /**
     * @ORM\OneToOne(targetEntity="Item")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    private $item;


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
     * Set characteristics
     *
     * @param Characteristics $characteristics
     * @return Stuff
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
     * Set type
     *
     * @param StuffType $type
     * @return Stuff
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return StuffType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set item
     *
     * @param Item $item
     * @return Stuff
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
}
