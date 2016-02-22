<?php

namespace SpikeTeam\UserBundle\Helper;

use SpikeTeam\UserBundle\Entity\Spiker;

class SpikerSignupHelper
{
    protected $mailer;

    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }

    //executes all logic associated with a new signup
    public function newSignup(Spiker $spiker)
    {
        $this->sendEmailToCaptain($spiker);
    }

    private function sendEmailToCaptain(Spiker $spiker)
    {
        $spikerName = $spiker->getFullName();
        $spikerEmail = $spiker->getEmail();
        $spikerDay = $spiker->getGroup()->getName();
        $captainEmail = $spiker->getGroup()->getCaptain()->getEmail();

        $message = \Swift_Message::newInstance()
                ->setSubject("New $spikerDay Spike Team Member:  $spikerName")
                ->setFrom('tester@crisistextline.org')
                ->setTo($captainEmail)
                ->setBody("$spikerName has joined your Spike Team! \n" .
                    "Send them a welcome email at: $spikerEmail"
                )
        ;

        $this->mailer->send($message);
    }
}
