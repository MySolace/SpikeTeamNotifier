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
                // NEED TO HANDLE UNREAL PHONE NUMBERS - TRY TRY/CATCH HERE?
                $this->sendMessage($spiker->getPhoneNumber());
            }
        }
    }

    public function sendMessage($phoneNumber)
    {
        $settingRepo = $this->em->getRepository('SpikeTeamSettingBundle:Setting');
        // Pulling CTL Twilio credentials from settings in db
        $sid = $settingRepo->findOneByName('twilio_sid')->getSetting();
        $token = $settingRepo->findOneByName('twilio_token')->getSetting();
        $message = $settingRepo->findOneByName('twilio_message')->getSetting();
        $client = new Services_Twilio($sid, $token);

        $twilioSend = $client->account->messages->create(array(
            "From" => $settingRepo->findOneByName('twilio_number')->getSetting(),
            "To" => $phoneNumber,
            "Body" => $message,
        ));

        if (isset($twilioSend->code) && $twilioSend->code == 21211) {
            // Error, not valid phone number
        } else {
            // sucess!
        }
    }
}
