<?php

namespace SpikeTeam\UserBundle\Helper;

use SpikeTeam\UserBundle\Entity\Spiker;
use SpikeTeam\UserBundle\Entity\SpikerGroup;


/**
 * UserHelper, in container as spike_team.user_helper
 */
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
     * Do all the things to set a user as the captain of a group
     *
     * @param Spiker $captain
     * @param SpikerGroup $group
     * @return boolean
     */
    public function setCaptain(Spiker $captain, SpikerGroup $group)
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

        // remove captain status from previously captained group(s), if so
        $gRepo = $this->em->getRepository('SpikeTeamUserBundle:SpikerGroup');
        $oldGroups = $gRepo->findByCaptain($captain);
        foreach($oldGroups as $oldGroup) {
            if ($oldGroup !== $group
                && $oldGroup->getCaptain() == $captain) {
                $oldGroup->setCaptain();
                $this->em->persist($oldGroup);
                $this->em->flush();
            }
        }

        if ($captain->getGroup() !== $group) {
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
