<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Question.
 *
 * @ORM\Table()
 * @ORM\Entity
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
     * @ORM\Column(name="title", type="string", length=511)
     */
    private $title;

    /**
     * @var bool
     *
     * @ORM\Column(name="required", type="boolean")
     */
    private $required;

    /**
     * @var bool
     *
     * @ORM\Column(name="multiple", type="boolean")
     */
    private $multiple;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="question")
     */
    private $answers;

    /**
     * @ORM\ManyToOne(targetEntity="Survey", inversedBy="questions")
     */
    private $survey;

    /**
     * Create new Question.
     */
    public function __construct()
    {
        $this->answers = new ArrayCollection();
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
     * @return Question
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
     * Set required.
     *
     * @param bool $required
     *
     * @return Question
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Get required.
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set multiple.
     *
     * @param bool $multiple
     *
     * @return Question
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * Get multiple.
     *
     * @return bool
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * Add answers.
     *
     * @param \AppBundle\Entity\Answer $answers
     *
     * @return Question
     */
    public function addAnswer(\AppBundle\Entity\Answer $answers)
    {
        $this->answers[] = $answers;

        return $this;
    }

    /**
     * Remove answers.
     *
     * @param \AppBundle\Entity\Answer $answers
     */
    public function removeAnswer(\AppBundle\Entity\Answer $answers)
    {
        $this->answers->removeElement($answers);
    }

    /**
     * Get answers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Set question.
     *
     * @param \AppBundle\Entity\Survey $question
     *
     * @return Question
     */
    public function setQuestion(\AppBundle\Entity\Survey $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question.
     *
     * @return \AppBundle\Entity\Survey
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set survey.
     *
     * @param \AppBundle\Entity\Survey $survey
     *
     * @return Question
     */
    public function setSurvey(\AppBundle\Entity\Survey $survey = null)
    {
        $this->survey = $survey;

        return $this;
    }

    /**
     * Get survey.
     *
     * @return \AppBundle\Entity\Survey
     */
    public function getSurvey()
    {
        return $this->survey;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Question
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the number of users who provided at least one vote for this question
     *
     * @return integer
     */
    public function getVoterCount()
    {
        $voters = [];

        foreach ($this->answers as $answer) {
            foreach ($answer->getVotes() as $vote) {
                $voters[$vote->getUser()->getId()] = true;
            }
        }

        return count($voters);
    }
}
