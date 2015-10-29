<?php

namespace SpikeTeam\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * SpikerGroupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SpikerRepository extends EntityRepository
{
    /**
     * Returns Spikers from enabled groups
     */
    public function findByEnabledGroup()
    {
        $qb = $this->createQueryBuilder('s');
        $qb->join('s.group', 'g')
            ->where('g.enabled = 1');

        try {
            return $qb->getQuery()->getResult();
        }  catch(\Doctrine\ORM\NoResultException $e) {
            return false;
        }
    }

    /**
     * Returns Spikers from enabled groups
     */
    public function findNonCaptain()
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.isCaptain is null OR s.isCaptain <> 1');

        try {
            return $qb->getQuery()->getResult();
        }  catch(\Doctrine\ORM\NoResultException $e) {
            return false;
        }
    }
}
