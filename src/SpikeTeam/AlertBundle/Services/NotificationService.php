<?php

namespace SpikeTeam\AlertBundle\Services;

use Services_Twilio;

class NotificationService
{
    protected $em;
    protected $host;
    protected $apiKey;
    protected $router;

    public function __construct($em, $router, $host, $apiKey)
    {
        $this->em = $em;
        $this->router = $router;
        $this->host = $host;
        $this->apiKey = $apiKey;

        $this->settingRepo = $this->em->getRepository('SpikeTeamSettingBundle:Setting');
        // Pulling CTL Twilio credentials from settings in db
        $this->sid = $this->settingRepo->findOneByName('twilio_sid')->getSetting();
        $this->token = $this->settingRepo->findOneByName('twilio_token')->getSetting();
        $this->message = $this->settingRepo->findOneByName('twilio_message')->getSetting();
        $this->from = $this->settingRepo->findOneByName('twilio_number')->getSetting();
    }

    public function sendMessage($phoneNumber)
    {
        $client = new Services_Twilio($this->sid, $this->token);

        try {
            $twilioSend = $client->account->messages->create(array(
                "From" => $this->from,
                "To" => $phoneNumber,
                "Body" => $this->message,
            ));
        } catch (\Services_Twilio_RestException $e) {
            if ($e->getStatus() != 200) {
                $recipient = $this->em
                                  ->getRepository('SpikeTeamUserBundle:Spiker')
                                  ->findOneByPhoneNumber($phoneNumber);

                $recipient->setIsEnabled(false);
                $this->em->persist($recipient);
                $this->em->flush();
            }
        }
    }

    public function sendCall($phoneNumber, $sendTextOnFail = false)
    {
        $client = new Services_Twilio($this->sid, $this->token);

        //callback url for delivering a message
        $url = $this->router->generate(
            'alert_message',
            array(
                'Message' => $this->message,
                'api_key' => $this->apiKey
            ),
            true
        );

        $params = array('Timeout' => 20, 'IfMachine' => 'Hangup');

        if ($sendTextOnFail) {
            //status callback url to check result of the call
            $statusCallbackUrl = $this->router->generate(
                'alert_callback',
                array(
                    'api_key' => $this->apiKey
                ),
                true
            );

            $params['StatusCallback'] = $statusCallbackUrl;
        }

        $client->account->calls->create(
            $this->from,
            $phoneNumber,
            $url,
            $params
        );
    }
}
