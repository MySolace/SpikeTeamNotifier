<?php

namespace SpikeTeam\ButtonBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;

use SpikeTeam\ButtonBundle\Entity\ButtonPush;

class ButtonControllerTest extends WebTestCase
{
    public function setup()
    {
        $this->client = static::createClient();
        $session = $this->client->getContainer()->get('session');
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
        $this->user = $this->em
                           ->getRepository('SpikeTeam\UserBundle\Entity\Admin')
                           ->findOneByEmail('admin@sample.com');
        $firewall = 'main';
        $token = new UsernamePasswordToken($this->user, null, $firewall, $this->user->getRoles());
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function testButtonOptions()
    {
        $currentDay = new \DateTime();
        $currentDay->modify('-6 hours');
        $currentDay = $currentDay->format('l');
        $crawler = $this->client->request('GET', '/');
        //Test there are two possible options for button push
        $this->assertEquals(2, $crawler->filter('option')->count());
        //Test that all is one of the options
        $this->assertEquals(1, $crawler->filter("option:contains('All')")->count());
        //Test that today's current day -6 hours is the other option
        $this->assertEquals(1, $crawler->filter("option:contains(${currentDay})")->count());
    }

    public function testButtonDisabled()
    {
        $currentDay = new \DateTime();
        $currentDay->modify('-6 hours');
        $currentDay = $currentDay->format('l');
        $spikeGroup = $this->em
                           ->getRepository('SpikeTeam\UserBundle\Entity\SpikerGroup')
                           ->findOneByName($currentDay);

        for ($i = 0; $i < 2; $i++) {
            $buttonPush = new ButtonPush($this->user->getId());
            $spikeGroup->addPush($buttonPush);
            $buttonPush->setGroup($spikeGroup);
            $this->em->persist($buttonPush);
            $this->em->persist($spikeGroup);
        }

        $crawler = $this->client->request('GET', '/');

        $this->assertEquals(1, $crawler->filter('div#button.disabled')->count());
    }

    public function testButtonEnabled()
    {
        $date = new \DateTime();
        $date->modify('-25 hours');
        $currentDay = new \DateTime();
        $currentDay->modify('-6 hours');
        $currentDay = $currentDay->format('l');
        $spikeGroup = $this->em
                           ->getRepository('SpikeTeam\UserBundle\Entity\SpikerGroup')
                           ->findOneByName($currentDay);

        for ($i = 0; $i < 2; $i++) {
            $buttonPush = new ButtonPush($this->user->getId());
            $buttonPush->setPushTime($date);
            $spikeGroup->addPush($buttonPush);
            $buttonPush->setGroup($spikeGroup);
            $this->em->persist($buttonPush);
            $this->em->persist($spikeGroup);
        }

        $crawler = $this->client->request('GET', '/');
        $this->assertEquals(1, $crawler->filter('div#button.enabled')->count());
    }

    public function testLatestMessage()
    {
        $currentDay = new \DateTime();
        $currentDay->modify('-6 hours');
        $currentDay = $currentDay->format('l');
        $spikeGroup = $this->em
                           ->getRepository('SpikeTeam\UserBundle\Entity\SpikerGroup')
                           ->findOneByName($currentDay);

        $buttonPush = new ButtonPush($this->user->getId());
        $spikeGroup->addPush($buttonPush);
        $buttonPush->setGroup($spikeGroup);
        $this->em->persist($buttonPush);
        $this->em->persist($spikeGroup);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/');

        $this->assertEquals(1, $crawler->filter(".latest:contains(${currentDay})")->count());
    }
}
