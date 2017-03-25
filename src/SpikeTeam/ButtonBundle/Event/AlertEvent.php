<?php

namespace SpikeTeam\ButtonBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class AlertEvent extends Event
{
    protected $spikers;
    protected $public;

    public function __construct($spikers, $public)
    {
        $this->spikers = $spikers;
        $this->public = $public;
    }

    public function getSpikers()
    {
        return $this->spikers;
    }

    public function pushIsPublic()
    {
        return $this->public;
    }
}
