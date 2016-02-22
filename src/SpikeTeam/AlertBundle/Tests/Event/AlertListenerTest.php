<?php

namespace SpikeTeam\AlertBundle\Tests\Event;

use SpikeTeam\AlertBundle\Event\AlertListener;

class AlertListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->spikerText = $this->emptyMock('\SpikeTeam\UserBundle\Entity\Spiker');
        $this->spikerText->method('getIsEnabled')->willReturn(true);
        $this->spikerText->method('getPhoneNumber')->willReturn('1234567890');
        $this->spikerText->method('getNotificationPreference')->willReturn(0);
        $this->spikerCall = $this->emptyMock('\SpikeTeam\UserBundle\Entity\Spiker');
        $this->spikerCall->method('getIsEnabled')->willReturn(true);
        $this->spikerCall->method('getPhoneNumber')->willReturn('1122334455');
        $this->spikerCall->method('getNotificationPreference')->willReturn(1);
        $this->spikerBoth = $this->emptyMock('\SpikeTeam\UserBundle\Entity\Spiker');
        $this->spikerBoth->method('getIsEnabled')->willReturn(true);
        $this->spikerBoth->method('getPhoneNumber')->willReturn('5544332211');
        $this->spikerBoth->method('getNotificationPreference')->willReturn(2);

        $this->spikers = array($this->spikerText, $this->spikerCall, $this->spikerBoth);
        $this->admin = $this->emptyMock('\SpikeTeam\UserBundle\Entity\Admin');
        $this->admin->method('getIsEnabled')->willReturn(true);
        $this->admin->method('getPhoneNumber')->willReturn('0987654321');
        $this->admins = array($this->admin);

        $adminRepository = $this->emptyMock('\SpikeTeam\UserBundle\Entity\SpikerRepository');
        $adminRepository->method('findAll')->willReturn($this->admins);

        $this->em = $this->emptyMock('\Doctrine\ORM\EntityManager');
        $this->em
            ->method('getRepository')
            ->with('SpikeTeamUserBundle:Admin')
            ->willReturn($adminRepository);

        $this->alertEvent = $this->emptyMock('SpikeTeam\ButtonBundle\Event\AlertEvent');
        $this->alertEvent
            ->method('getSpikers')
            ->willReturn($this->spikers);

        $this->notificationService = $this->emptyMock('SpikeTeam\AlertBundle\Services\NotificationService');
        $this->notificationService->method('sendMessage')->willReturn(NULL);
        $this->notificationService->method('sendCall')->willReturn(NULL);
        $this->alertListener = new AlertListener($this->em, $this->notificationService);
    }

    public function testOnAlert() {
        $this
            ->notificationService
            ->expects($this->at(0))
            ->method('sendMessage')
            ->with('1234567890');

        $this
            ->notificationService
            ->expects($this->at(1))
            ->method('sendCall')
            ->with('1122334455', false);

        $this
            ->notificationService
            ->expects($this->at(2))
            ->method('sendCall')
            ->with('5544332211', true);

        $this
            ->notificationService
            ->expects($this->at(3))
            ->method('sendMessage')
            ->with('0987654321');

        $this->alertListener->onAlert($this->alertEvent);
    }


    private function emptyMock($klass, $methods=array()) {
        return $this
            ->getMockBuilder($klass)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
