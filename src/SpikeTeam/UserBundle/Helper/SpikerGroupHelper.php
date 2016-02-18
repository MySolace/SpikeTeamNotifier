<?php

namespace SpikeTeam\UserBundle\Helper;

class SpikerGroupHelper
{
    public function getCurrentGroupId($date = null)
    {
        $date = ($date) ? $date : new \DateTime();
        $groupId = $date->modify("-6 hours")->format('N') + 1;
        $groupId = ($groupId > 7) ? 1 : $groupId;

        return $groupId;
    }
}
