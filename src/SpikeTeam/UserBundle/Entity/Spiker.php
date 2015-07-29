<?php

namespace SpikeTeam\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use SpikeTeam\UserBundle\Entity\SpikerGroup;

/**
 * Spiker
 *
 * @ORM\Table(name="spiker")
 * @ORM\Entity()
 */
class Spiker
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
     * @ORM\Column(name="first_name", type="string", length=60)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=60)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=11, unique=true)
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", unique=true, nullable=true)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="SpikerGroup", inversedBy="spikers", cascade={"persist"})
     */
    private $group;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_supervisor", type="boolean")
     */
    private $isSupervisor;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled;

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
     * Set firstName
     *
     * @param string $firstName
     * @return Spiker
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return Spiker
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     * @return Spiker
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Spiker
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set group
     *
     * @param SpikerGroup $group
     *
     * @return Spiker
     */
    public function setGroup(SpikerGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return SpikerGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set isSupervisor
     *
     * @param boolean $isSupervisor
     * @return Spiker
     */
    public function setIsSupervisor($isSupervisor)
    {
        $this->isSupervisor = $isSupervisor;

        return $this;
    }

    /**
     * Get isSupervisor
     *
     * @return boolean
     */
    public function getIsSupervisor()
    {
        return $this->isSupervisor;
    }

    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     * @return Spiker
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * Get isEnabled
     *
     * @return boolean
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }
}
