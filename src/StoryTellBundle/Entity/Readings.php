<?php

namespace StoryTellBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Readings
 *
 * @ORM\Table(name="readings")
 * @ORM\Entity(repositoryClass="StoryTellBundle\Repository\ReadingsRepository")
 */
class Readings
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="StoryTellBundle\Entity\Story")
     * @ORM\JoinColumn(name="story_id", referencedColumnName="id")
     */
    private $story;

    /**
     * @ORM\ManyToOne(targetEntity="StoryTellBundle\Entity\StoryChapter")
     * @ORM\JoinColumn(name="story_chapter_id", referencedColumnName="id")
     */
    private $storyChapter;

    /**
     * @ORM\ManyToOne(targetEntity="StoryTellBundle\Entity\StoryContent")
     * @ORM\JoinColumn(name="story_content_id", referencedColumnName="id")
     */
    private $storyContent;

    /**
     * @var int
     *
     * @ORM\Column(name="is_finished", type="boolean")
     */
    private $isFinished;


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
     * Set user
     *
     * @param User $user
     * @return Readings
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
     * Set story
     *
     * @param Story $story
     * @return Readings
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
     * Set storyChapter
     *
     * @param StoryChapter $storyChapter
     * @return Readings
     */
    public function setStoryChapter($storyChapter)
    {
        $this->storyChapter = $storyChapter;

        return $this;
    }

    /**
     * Get storyChapter
     *
     * @return StoryChapter
     */
    public function getStoryChapter()
    {
        return $this->storyChapter;
    }

    /**
     * Set storyContent
     *
     * @param StoryContent $storyContent
     * @return Readings
     */
    public function setStoryContent($storyContent)
    {
        $this->storyContent = $storyContent;

        return $this;
    }

    /**
     * Get storyContent
     *
     * @return StoryContent
     */
    public function getStoryContent()
    {
        return $this->storyContent;
    }

    /**
     * Set isFinished
     *
     * @param integer $isFinished
     * @return Readings
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
}
