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
        $admins = $this->em->getRepository('SpikeTeamUserBundle:Admin')->findAll();
        $recipients = array_merge($event->getSpikers(), $admins);
        foreach($recipients as $recipient) {
            if ($recipient->getIsEnabled() && $recipient->getPhoneNumber()) {
                $this->sendMessage($recipient);
            }
        }
    }

    public function sendMessage($recipient)
    {
        $settingRepo = $this->em->getRepository('SpikeTeamSettingBundle:Setting');
        // Pulling CTL Twilio credentials from settings in db
        $sid = $settingRepo->findOneByName('twilio_sid')->getSetting();
        $token = $settingRepo->findOneByName('twilio_token')->getSetting();
        $message = $settingRepo->findOneByName('twilio_message')->getSetting();
        $from = $settingRepo->findOneByName('twilio_number')->getSetting();
        $client = new Services_Twilio($sid, $token);

        try {
            $twilioSend = $client->account->messages->create(array(
                "From" => $from,
                "To" => $recipient->getPhoneNumber(),
                "Body" => $message,
            ));       
        } catch (\Services_Twilio_RestException $e) {
            if ($e->getStatus() != 200) {
                $recipient->setIsEnabled(false);
                $this->em->persist($recipient);
                $this->em->flush();
            }
        }

    }
}
