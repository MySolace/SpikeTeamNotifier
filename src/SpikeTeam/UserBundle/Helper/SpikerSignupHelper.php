<?php

namespace SpikeTeam\UserBundle\Helper;

use SpikeTeam\UserBundle\Entity\Spiker;

class SpikerSignupHelper
{
    protected $mailer;
    protected $config;

    public function __construct($mailer, $config)
    {
        $this->mailer = $mailer;
        $this->config = $config;
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
        $captain = $spiker->getGroup()->getCaptain();
        $adminEmail = $this->config->get('admin_email', '');
        $toArray = array();

        if (is_null($captain)) {
            return;
        }

        $toArray[] = $captain->getEmail();

        if ($adminEmail != '') {
            $toArray[] = $adminEmail;
        }

        $message = \Swift_Message::newInstance()
                ->setSubject("New $spikerDay Spike Team Member:  $spikerName")
                ->setFrom('tester@crisistextline.org')
                ->setTo($toArray)
                ->setBody("$spikerName has joined your Spike Team! \n" .
                    "Send them a welcome email at: $spikerEmail"
                )
        ;

        $this->mailer->send($message);
    }
}
