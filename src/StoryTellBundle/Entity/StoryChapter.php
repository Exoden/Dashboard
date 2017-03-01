<?php

namespace StoryTellBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StoryChapter
 *
 * @ORM\Table(name="story_chapter")
 * @ORM\Entity(repositoryClass="StoryTellBundle\Repository\StoryChapterRepository")
 */
class StoryChapter
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
     * @ORM\ManyToOne(targetEntity="StoryTellBundle\Entity\Story")
     * @ORM\JoinColumn(name="story_id", referencedColumnName="id")
     */
    private $story;

    /**
     * @var int
     *
     * @ORM\Column(name="chapter", type="integer")
     */
    private $chapter;

    /**
     * @var int
     *
     * @ORM\Column(name="is_published", type="boolean", options={"default" : 0})
     */
    private $isPublished;


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
     * @return StoryChapter
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
     * Set story
     *
     * @param Story $story
     * @return StoryChapter
     */
    public function setStory($story)
    {
        $this->story = $story;

        return $this;
    }

    /**
     * Get story
     *
     * @return Story
     */
    public function getStory()
    {
        return $this->story;
    }

    /**
     * Set chapter
     *
     * @param integer $chapter
     * @return StoryChapter
     */
    public function setChapter($chapter)
    {
        $this->chapter = $chapter;

        return $this;
    }

    /**
     * Get chapter
     *
     * @return integer
     */
    public function getChapter()
    {
        return $this->chapter;
    }

    /**
     * Set isPublished
     *
     * @param integer $isPublished
     * @return StoryChapter
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
}
