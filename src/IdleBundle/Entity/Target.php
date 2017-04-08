<?php

namespace IdleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Target
 *
 * @ORM\Table(name="target")
 * @ORM\Entity(repositoryClass="IdleBundle\Repository\TargetRepository")
 */
class Target
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
     * @ORM\Column(name="currentHealth", type="integer")
     */
    private $currentHealth;

    /**
     * @ORM\OneToOne(targetEntity="Enemy")
     * @ORM\JoinColumn(name="enemy_id", referencedColumnName="id")
     */
    private $enemy;


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
     * Set currentHealth
     *
     * @param integer $currentHealth
     * @return Target
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
     * Set enemy
     *
     * @param Enemy $enemy
     * @return Target
     */
    public function setEnemy($enemy)
    {
        $this->enemy = $enemy;

        return $this;
    }

    /**
     * Get enemy
     *
     * @return Enemy
     */
    public function getEnemy()
    {
        return $this->enemy;
    }
}
