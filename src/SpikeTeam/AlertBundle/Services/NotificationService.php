<?php

namespace SpikeTeam\AlertBundle\Services;

use Services_Twilio;

class NotificationService
{
    protected $em;
    protected $apiKey;
    protected $router;

    public function __construct($em, $router, $apiKey)
    {
        $this->em = $em;
        $this->router = $router;
        $this->apiKey = $apiKey;

        $this->settingRepo = $this->em->getRepository('SpikeTeamSettingBundle:Setting');
    }

    public function sendMessage($phoneNumber)
    {
        $client = $this->setupTwilioClient();
        $message = $this->settingRepo->findOneByName('twilio_message')->getSetting();
        $from = $this->settingRepo->findOneByName('twilio_number')->getSetting();

        try {
            $twilioSend = $client->account->messages->create(array(
                "From" => $from,
                "To" => $phoneNumber,
                "Body" => $message,
            ));
        } catch (\Services_Twilio_RestException $e) {
            if ($e->getStatus() != 200) {
                $this->disableNumber($phoneNumber, 'Spiker');
                $this->disableNumber($phoneNumber, 'Admin');
            }
        }
    }

    public function sendCall($phoneNumber, $sendTextOnFail = false)
    {
        $client = $this->setupTwilioClient();
        $message = $this->settingRepo->findOneByName('twilio_message')->getSetting();
        $from = $this->settingRepo->findOneByName('twilio_number')->getSetting();

        //callback url for delivering a message
        $url = $this->router->generate(
            'alert_message',
            array(
                'Message' => $message,
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
            $from,
            $phoneNumber,
            $url,
            $params
        );
    }

    private function setupTwilioClient()
    {
        $sid = $this->settingRepo->findOneByName('twilio_sid')->getSetting();
        $token = $this->settingRepo->findOneByName('twilio_token')->getSetting();

        return new Services_Twilio($sid, $token);
    }

    private function disableNumber($phoneNumber, $type)
    {
        $recipient = $this->em
                          ->getRepository('SpikeTeamUserBundle:'.$type)
                          ->findOneByPhoneNumber($phoneNumber);

        if ($recipient) {
            $recipient->setIsEnabled(false);
            $this->em->persist($recipient);
        }

        $this->em->flush();
    }
}
