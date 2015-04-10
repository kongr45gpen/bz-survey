<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class User implements UserInterface, \Serializable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="bzid", type="string", length=255, unique=true)
     */
    private $bzid;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255)
     */
    private $username;

    /**
     * @ORM\ManyToMany(targetEntity="Survey")
     */
    private $surveys;


    /**
     * Create new Survey
     */
    public function __construct()
    {
        $this->surveys = new ArrayCollection();
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
     * Set bzid
     *
     * @param string $bzid
     * @return User
     */
    public function setBzid($bzid)
    {
        $this->bzid = $bzid;

        return $this;
    }

    /**
     * Get bzid
     *
     * @return string
     */
    public function getBzid()
    {
        return $this->bzid;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Get password
     *
     * @return null
     */
    public function getPassword()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        return array('ROLE_USER');
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {
        $this->token = null;
    }

    /**
     * Add surveys
     *
     * @param \AppBundle\Entity\Survey $surveys
     * @return User
     */
    public function addSurvey(\AppBundle\Entity\Survey $surveys)
    {
        $this->surveys[] = $surveys;

        return $this;
    }

    /**
     * Remove surveys
     *
     * @param \AppBundle\Entity\Survey $surveys
     */
    public function removeSurvey(\AppBundle\Entity\Survey $surveys)
    {
        $this->surveys->removeElement($surveys);
    }

    /**
     * Find out whether a user has participated in a survey
     *
     * @param \AppBundle\Entity\Survey $survey
     * @return User
     */
    public function hasSurvey(\AppBundle\Entity\Survey $survey)
    {
        return $this->surveys->exists(function($i, Survey $p) use ($survey) {
            return $p->getId() === $survey->getId();
        });
    }

    /**
     * Get surveys
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSurveys()
    {
        return $this->surveys;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->bzid,
            $this->username,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->bzid,
            $this->username,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }
}
