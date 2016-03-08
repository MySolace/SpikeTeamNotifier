<?php

namespace SpikeTeam\UserBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use SpikeTeam\UserBundle\Entity\SpikerGroup;
use SpikeTeam\ButtonBundle\Entity\ButtonPush;

class SpikerGroupTest extends WebTestCase
{
    public function setUp()
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
    }

    public function testSpikerGroupGetRecentPushesCount()
    {
        $em = $this->container->get('doctrine')->getManager();

        $user = $em->getRepository('SpikeTeam\UserBundle\Entity\Admin')
                   ->findOneByEmail('admin@sample.com');

        $spikerGroup = new SpikerGroup();
        $buttonPush = new ButtonPush($user->getId());
        $buttonPush->setPushTime(new \DateTime());
        $spikerGroup->addPush($buttonPush);

        $this->assertEquals($spikerGroup->getRecentPushesCount(), 1);

        $buttonPush = new ButtonPush($user->getId());
        $date = new \DateTime();
        $date->modify('-2 days');

        $buttonPush->setPushTime($date);
        $spikerGroup->addPush($buttonPush);

        $this->assertEquals($spikerGroup->getRecentPushesCount(), 1);

        $buttonPush = new ButtonPush($user->getId());
        $date = new \DateTime();
        $date->modify('-12 hours');

        $buttonPush->setPushTime($date);
        $spikerGroup->addPush($buttonPush);

        $this->assertEquals($spikerGroup->getRecentPushesCount(), 2);
    }
}
