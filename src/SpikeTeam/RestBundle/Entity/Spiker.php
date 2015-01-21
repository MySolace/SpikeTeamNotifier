<?php

namespace SpikeTeam\RestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Spiker
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="SpikeTeam\RestBundle\Entity\SpikerRepository")
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
