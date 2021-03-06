<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Survey.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Survey
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
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * @var bool
     *
     * @ORM\Column(name="showResults", type="boolean")
     */
    private $showResults;

    /**
     * @var bool
     *
     * @ORM\Column(name="allowResetting", type="boolean")
     */
    private $allowResetting;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $owner;

    /**
     * @var bool
     *
     * @ORM\Column(name="showParticipants", type="boolean")
     */
    private $showParticipants;

    /**
     * @ORM\OneToMany(targetEntity="Question", mappedBy="survey")
     */
    private $questions;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="surveys")
     */
    private $users;

    /**
     * Create new Survey.
     */
    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->users     = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Survey
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Survey
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add questions.
     *
     * @param \AppBundle\Entity\Question $questions
     *
     * @return Survey
     */
    public function addQuestion(\AppBundle\Entity\Question $questions)
    {
        $this->questions[] = $questions;

        return $this;
    }

    /**
     * Remove questions.
     *
     * @param \AppBundle\Entity\Question $questions
     */
    public function removeQuestion(\AppBundle\Entity\Question $questions)
    {
        $this->questions->removeElement($questions);
    }

    /**
     * Get questions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Set enabled.
     *
     * @param bool $enabled
     *
     * @return Survey
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled.
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set showResults.
     *
     * @param bool $showResults
     *
     * @return Survey
     */
    public function setShowResults($showResults)
    {
        $this->showResults = $showResults;

        return $this;
    }

    /**
     * Get showResults.
     *
     * @return bool
     */
    public function getShowResults()
    {
        return $this->showResults;
    }

    /**
     * Get whether to show the participant names in the survey results
     *
     * @return mixed
     */
    public function getShowParticipants()
    {
        return $this->showParticipants;
    }

    /**
     * Set whether to show the participant names in the survey results
     *
     * @param mixed $showParticipants
     *
     * @return self
     */
    public function setShowParticipants($showParticipants)
    {
        $this->showParticipants = $showParticipants;

        return $this;
    }


    /**
     * Get whether this survey allows users to delete their answers and submit
     * it again
     *
     * @return bool
     */
    public function getAllowResetting()
    {
        return $this->allowResetting;
    }

    /**
     * @param bool $allowRetaking
     *
     * @return self
     */
    public function setAllowResetting($allowResetting)
    {
        $this->allowResetting = $allowResetting;

        return $this;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set owner
     *
     * @param User $owner
     *
     * @return self
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Add users.
     *
     * @param \AppBundle\Entity\User $users
     *
     * @return Survey
     */
    public function addUser(\AppBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users.
     *
     * @param \AppBundle\Entity\User $users
     */
    public function removeUser(\AppBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
