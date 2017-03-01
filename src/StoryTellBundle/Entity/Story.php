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

    // Notez le singulier, on ajoute une seule catégorie à la fois
    public function addStoryGenre(StoryGenre $genre)
    {
        // Ici, on utilise l'ArrayCollection vraiment comme un tableau
        $this->genres[] = $genre;

        return $this;
    }

    public function removeStoryGenre(StoryGenre $genre)
    {
        // Ici on utilise une méthode de l'ArrayCollection, pour supprimer la catégorie en argument
        $this->genres->removeElement($genre);
    }

    // Notez le pluriel, on récupère une liste de catégories ici !
    public function getGenres()
    {
        return $this->genres;
    }
}
