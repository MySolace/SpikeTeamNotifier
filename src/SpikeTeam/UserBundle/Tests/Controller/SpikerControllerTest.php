<?php

namespace SpikeTeam\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;

use SpikeTeam\UserBundle\Entity\Spiker;

class SpikerControllerTest extends WebTestCase
{
    public function setup()
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
    }

    public function testSpikerSignupSuccess()
    {
        $time = time();
        $this->client->enableProfiler();
        $spikeCaptain = new Spiker();
        $spikeCaptain->setEmail("testcaptain+${time}@crisistextline.org");
        $spikeCaptain->setPhoneNumber('9' + substr($time, 1));
        $spikeCaptain->setIsSupervisor(false);
        $spikeCaptain->setIsEnabled(true);

        $spikerGroup = $this->em
                            ->getRepository('SpikeTeam\UserBundle\Entity\SpikerGroup')
                            ->find(1);

        $spikerGroup->setCaptain($spikeCaptain);
        $this->em->persist($spikeCaptain);
        $this->em->persist($spikerGroup);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/spikers/signup');
        $form = $crawler->selectButton('spiketeam_userbundle_spiker[save]')->form(
            array(
                "spiketeam_userbundle_spiker[firstName]"    => "Test",
                "spiketeam_userbundle_spiker[lastName]"     =>  "User",
                "spiketeam_userbundle_spiker[phoneNumber]"  =>  $time,
                "spiketeam_userbundle_spiker[email]"        =>  "testuser+${time}@crisistextline.org"
            )
        );

        $crawler = $this->client->submit($form);
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');

        $this->assertEquals(1, $crawler->filter("div.success")->count());

        //Test that spike captain email went out
        $this->assertEquals(1, $mailCollector->getMessageCount());
    }

    public function testPreventDuplicateSpikerSignup()
    {
        $time = time();
        $crawler = $this->client->request('GET', '/spikers/signup');
        $form = $crawler->selectButton('spiketeam_userbundle_spiker[save]')->form(
            array(
                "spiketeam_userbundle_spiker[firstName]"    => "Test",
                "spiketeam_userbundle_spiker[lastName]"     =>  "User",
                "spiketeam_userbundle_spiker[phoneNumber]"  =>  $time,
                "spiketeam_userbundle_spiker[email]"        =>  "testuser+${time}@crisistextline.org"
            )
        );

        $crawler = $this->client->submit($form);

        $crawler = $this->client->request('GET', '/spikers/signup');
        $form = $crawler->selectButton('spiketeam_userbundle_spiker[save]')->form(
            array(
                "spiketeam_userbundle_spiker[firstName]"    => "Test",
                "spiketeam_userbundle_spiker[lastName]"     =>  "User",
                "spiketeam_userbundle_spiker[phoneNumber]"  =>  $time,
                "spiketeam_userbundle_spiker[email]"        =>  "testuser+${time}@crisistextline.org"
            )
        );

        $crawler = $this->client->submit($form);

        $this->assertEquals(1, $crawler->filter("div.errors:contains('email')")->count());
        $this->assertEquals(1, $crawler->filter("div.errors:contains('phone')")->count());
    }
}
