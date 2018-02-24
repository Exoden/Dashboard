<?php

namespace PickOneBundle\Entity;

use AppBundle\Entity\Language;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Question
 *
 * @ORM\Table(name="question")
 * @ORM\Entity(repositoryClass="PickOneBundle\Repository\QuestionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Question
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
     * @var int
     *
     * @ORM\Column(name="nb_votes", type="integer")
     */
    private $nbVotes;

    /**
     * @ORM\ManyToMany(targetEntity="PickOneBundle\Entity\QuestionGenre")
     * @ORM\JoinTable(name="link_question_genre",
     *      joinColumns={@ORM\JoinColumn(name="question_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="question_genre_id", referencedColumnName="id")}
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
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="question", cascade={"persist", "remove"})
     */
    private $answers;

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


    public function __construct() {
        $this->genres = new ArrayCollection();
        $this->answers = new ArrayCollection();
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
     * @return Question
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
     * Set nbVotes
     *
     * @param integer $nbVotes
     * @return Question
     */
    public function setNbVotes($nbVotes)
    {
        $this->nbVotes = $nbVotes;

        return $this;
    }

    /**
     * Get nbVotes
     *
     * @return integer 
     */
    public function getNbVotes()
    {
        return $this->nbVotes;
    }

    /**
     * Set author
     *
     * @param User $author
     * @return Question
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
     * @param Language $language
     * @return Question
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Question
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
     * @return Question
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
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt(new \Datetime());
        }
        if ($this->getNbVotes() == null) {
            $this->setNbVotes(0);
        }
        $this->setUpdatedAt(new \DateTime());
    }


    public function addQuestionGenre(QuestionGenre $genre)
    {
        $this->genres[] = $genre;

        return $this;
    }

    public function removeQuestionGenre(QuestionGenre $genre)
    {
        $this->genres->removeElement($genre);
    }

    public function getGenres()
    {
        return $this->genres;
    }


    public function addAnswer(Answer $answer)
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;

            $answer->setQuestion($this);
        }
        return $this;
    }

    public function removeAnswer(Answer $answer)
    {
        $this->answers->removeElement($answer);
    }

    public function getAnswers()
    {
        return $this->answers;
    }
}
