<?php

namespace SpikeTeam\ButtonBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class AlertEvent extends Event
{
    protected $spikers;

    public function __construct($spikers)
    {
        $this->spikers = $spikers;
    }

    public function getSpikers()
    {
        return $this->spikers;
    }
}
