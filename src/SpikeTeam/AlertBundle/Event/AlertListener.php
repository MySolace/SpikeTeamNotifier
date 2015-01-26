<?php

namespace SpikeTeam\AlertBundle\Event;

use SpikeTeam\ButtonBundle\Event\AlertEvent;
use Services_Twilio;

class AlertListener
{

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onAlert(AlertEvent $event)
    {
        foreach($event->getSpikers() as $spiker) {
            $this->sendMessage($spiker->getPhoneNumber());
        }
    }

    public function sendMessage($phoneNumber)
    {
        // Pulling CTL Twilio credentials from parameters.yml
        $sid = $this->container->getParameter('twilio_sid');
        $token = $this->container->getParameter('twilio_tok');
        $message = $this->container->getParameter('twilio_msg');
        $client = new Services_Twilio($sid, $token);

        $twilioMessage = $client->account->messages->create(array(
            "From" => $this->container->getParameter('twilio_number_dev'),
            "To" => $phoneNumber,
            "Body" => $message,
        ));

        // return new Response($twilioMessage->sid);
    }
}
