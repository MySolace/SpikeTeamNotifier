<?php

namespace SpikeTeam\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use SpikeTeam\UserBundle\Entity\Spiker;
use SpikeTeam\ButtonBundle\Entity\ButtonPush;

/**
 * SpikerGroup
 *
 * @ORM\Table(name="spiker_group")
 * @ORM\Entity(repositoryClass="SpikeTeam\UserBundle\Entity\SpikerGroupRepository")
 */
class SpikerGroup
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Spiker", mappedBy="group")
     */
    private $spikers;

    /**
     * @ORM\OneToMany(targetEntity="SpikeTeam\ButtonBundle\Entity\ButtonPush", mappedBy="group")
     */
    private $pushes;

    public function __construct()
    {
        $this->spikers = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return SpikerGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add spikers
     *
     * @param Spiker $spikers
     *
     * @return SpikerGroup
     */
    public function addSpiker(Spiker $spikers)
    {
        $this->spikers[] = $spikers;

        return $this;
    }

    /**
     * Remove spikers
     *
     * @param Spikers $spikers
     */
    public function removeSpiker(Spiker $spikers)
    {
        $this->spikers->removeElement($spikers);
    }

    /**
     * Get spikers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSpikers()
    {
        return $this->spikers;
    }

    /**
     * Add pushes
     *
     * @param ButtonPush $pushes
     *
     * @return SpikerGroup
     */
    public function addPush(ButtonPush $pushes)
    {
        $this->pushes[] = $pushes;

        return $this;
    }

    /**
     * Remove pushes
     *
     * @param ButtonPush $pushes
     */
    public function removePush(ButtonPush $pushes)
    {
        $this->pushes->removeElement($pushes);
    }

    /**
     * Get pushes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPushes()
    {
        return $this->pushes;
    }

}
