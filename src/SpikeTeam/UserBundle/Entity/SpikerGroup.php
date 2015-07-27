<?php

namespace SpikeTeam\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use SpikeTeam\UserBundle\Entity\Spiker;

/**
 * SpikerGroup
 *
 * @ORM\Table()
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

}
