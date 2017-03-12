<?php

namespace StoryTellBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Story
 *
 * @ORM\Table(name="story")
 * @ORM\Entity(repositoryClass="StoryTellBundle\Repository\StoryRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Story
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=500)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="StoryTellBundle\Entity\StoryGenre")
     * @ORM\JoinTable(name="link_story_genre",
     *      joinColumns={@ORM\JoinColumn(name="story_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="story_genre_id", referencedColumnName="id")}
     *      )
     */
    private $genres;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Language")
     * @ORM\JoinColumn(name="language_id", referencedColumnName="id")
     */
    private $language;

    /**
     * @var int
     *
     * @ORM\Column(name="is_published", type="boolean", options={"default" : 0})
     */
    private $isPublished;

    /**
     * @var int
     *
     * @ORM\Column(name="is_finished", type="boolean", options={"default" : 0})
     */
    private $isFinished;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="published_at", type="datetime", nullable=true)
     */
    private $publishedAt;


    public function __construct() {
        $this->genres = new ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Story
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Story
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set author
     *
     * @param User $author
     * @return Story
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set language
     *
     * @param User $language
     * @return Story
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return User
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set isPublished
     *
     * @param integer $isPublished
     * @return Story
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        if ($isPublished == true) {
            $this->setPublishedAt(new \Datetime());
        }

        return $this;
    }

    /**
     * Get isPublished
     *
     * @return integer
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Set isFinished
     *
     * @param integer $isFinished
     * @return Story
     */
    public function setIsFinished($isFinished)
    {
        $this->isFinished = $isFinished;

        return $this;
    }

    /**
     * Get isFinished
     *
     * @return integer
     */
    public function getIsFinished()
    {
        return $this->isFinished;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Story
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Story
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set publishedAt
     *
     * @param \DateTime $publishedAt
     * @return Story
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * Get publishedAt
     *
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }


    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \Datetime());
        }
        $this->setUpdatedAt(new \DateTime());
    }


    public function addStoryGenre(StoryGenre $genre)
    {
        $this->genres[] = $genre;

        return $this;
    }

    public function removeStoryGenre(StoryGenre $genre)
    {
        $this->genres->removeElement($genre);
    }

    public function getGenres()
    {
        return $this->genres;
    }
}
