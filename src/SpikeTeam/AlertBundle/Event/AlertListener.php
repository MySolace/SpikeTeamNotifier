<?php

namespace SpikeTeam\AlertBundle\Event;

use SpikeTeam\ButtonBundle\Event\AlertEvent;
use Services_Twilio;

class AlertListener
{

    protected $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function onAlert(AlertEvent $event)
    {
        foreach($event->getSpikers() as $spiker) {
            if ($spiker->getIsEnabled()) {
                $this->sendMessage($spiker);
            }
        }
    }

    public function sendMessage($spiker)
    {
        $settingRepo = $this->em->getRepository('SpikeTeamSettingBundle:Setting');
        // Pulling CTL Twilio credentials from settings in db
        $sid = $settingRepo->findOneByName('twilio_sid')->getSetting();
        $token = $settingRepo->findOneByName('twilio_token')->getSetting();
        $message = $settingRepo->findOneByName('twilio_message')->getSetting();
        $client = new Services_Twilio($sid, $token);

        try {
            $twilioSend = $client->account->messages->create(array(
                "From" => $settingRepo->findOneByName('twilio_number')->getSetting(),
                "To" => $spiker->getPhoneNumber(),
                "Body" => $message,
            ));       
        } catch (\Services_Twilio_RestException $e) {
            if ($e->getStatus() != 200) {
                $spiker->setIsEnabled(false);
                $this->em->persist($spiker);
                $this->em->flush();
            }
        }

    }
}
