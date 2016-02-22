<?php

namespace SpikeTeam\AlertBundle\Event;

use SpikeTeam\ButtonBundle\Event\AlertEvent;
use SpikeTeam\AlertBundle\Services\NotificationService;

class AlertListener
{
    protected $em;
    protected $notificationService;

    public function __construct($em, NotificationService $notificationService)
    {
        $this->em = $em;
        $this->notificationService = $notificationService;
    }

    public function onAlert(AlertEvent $event)
    {
        $admins = $this->em->getRepository('SpikeTeamUserBundle:Admin')->findAll();
        $recipients = array_merge($event->getSpikers(), $admins);
        foreach($recipients as $recipient) {
            if ($recipient->getIsEnabled() && $recipient->getPhoneNumber()) {
                $preference = $recipient->getNotificationPreference();
                $phoneNumber = $recipient->getPhoneNumber();

                switch ($preference) {
                    case 0:
                        $this->notificationService->sendMessage($phoneNumber);
                        break;
                    case 1:
                        $this->notificationService->sendCall($phoneNumber, false);
                        break;
                    case 2:
                        $this->notificationService->sendCall($phoneNumber, true);
                        break;
                    default:
                        break;
                }
            }
        }
    }
}
