<?php

namespace StoryTellBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StoryContent
 *
 * @ORM\Table(name="story_content")
 * @ORM\Entity(repositoryClass="StoryTellBundle\Repository\StoryContentRepository")
 */
class StoryContent
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
     * @ORM\ManyToOne(targetEntity="StoryTellBundle\Entity\StoryChapter")
     * @ORM\JoinColumn(name="story_chapter_id", referencedColumnName="id")
     */
    private $storyChapter;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=1000)
     */
    private $content;

    /**
     * @var int
     *
     * @ORM\Column(name="page", type="integer")
     */
    private $page;


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
     * Set storyChapter
     *
     * @param StoryChapter $storyChapter
     * @return StoryContent
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
     * Set content
     *
     * @param string $content
     * @return StoryContent
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set page
     *
     * @param integer $page
     * @return StoryContent
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return integer
     */
    public function getPage()
    {
        return $this->page;
    }
}
