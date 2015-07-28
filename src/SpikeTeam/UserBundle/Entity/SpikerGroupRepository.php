<?php

namespace SpikeTeam\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * SpikerGroupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SpikerGroupRepository extends EntityRepository
{
    /**
     * Returns Group with fewest members
     */
    public function findEmptiest()
    {
        $qb = $this->createQueryBuilder('g');
        $qb->addSelect('COUNT(s) AS HIDDEN spikerCount')
            ->join('g.spikers', 's')
            ->where('s.isEnabled = 1')
            ->groupBy('g')
            ->orderBy('spikerCount', 'ASC')
            ->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        }  catch(\Doctrine\ORM\NoResultException $e) {
            return false;
        }
    }

    /**
     * Returns array of ids for all current groups
     */
    public function getAllIds()
    {
        $stmt = $this->_em->getConnection()->prepare("SELECT GROUP_CONCAT(id) from spiker_group");
        $stmt->execute();

        try {
            return explode(',', $stmt->fetchAll()[0]['GROUP_CONCAT(id)']);
        }  catch(\Doctrine\ORM\NoResultException $e) {
            return false;
        }
    }

}
