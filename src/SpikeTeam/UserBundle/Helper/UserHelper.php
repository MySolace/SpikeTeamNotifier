<?php

namespace SpikeTeam\UserBundle\Helper;

use SpikeTeam\UserBundle\Entity\Spiker;
use SpikeTeam\UserBundle\Entity\SpikerGroup;

class UserHelper
{
    /**
     * A local copy of the entity manager
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * Constructor.
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Process number so that it will work w/ Twilio
     *
     * @param string $phoneNumber
     * @return string
     */
    public function processNumber($phoneNumber)
    {
        $return = false;
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        switch (strlen($phoneNumber)) {
            case 10:
                $return = '1' . $phoneNumber;
                break;
            case 11:
                $return = $phoneNumber;
                break;
        }
        return $return;
    }

    /**
     * Do all the things to set a user as the captain of a group
     *
     * @param Spiker $captain
     * @param SpikerGroup $group
     * @return boolean
     */
    public function setCaptain(Spiker $captain, SpikerGroup $group, SpikerGroup $oldGroup = null)
    {
        // flush out old captain for group
        $oldCaptain = $group->getCaptain();
        if (isset($oldCaptain)) {
            if ($oldCaptain->getId() !== $captain->getId()) {
                $oldCaptain->setIsCaptain(false);
                $this->em->persist($oldCaptain);
            } else {
                return false;
            }
        }

        // remove captain status from previously captained group, if so
        if ($oldGroup == null) {
            $oldGroup = $captain->getGroup();
        }
        if (isset($oldGroup) && $oldGroup !== $group) {
            if ($oldGroup->getCaptain() == $captain) {
                $oldGroup->setCaptain();
                $this->em->persist($oldGroup);
                $this->em->flush();
            }
            $captain->setGroup($group);
        }

        $group->setCaptain($captain);
        $captain->setIsCaptain(true);

        $this->em->persist($group);
        $this->em->persist($captain);
        $this->em->flush();

        return true;
    }
}
