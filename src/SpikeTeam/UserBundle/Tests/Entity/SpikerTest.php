<?php

namespace SpikeTeam\UserBundle\Tests\Entity;
use SpikeTeam\UserBundle\Entity\Spiker;

class SpikerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetPhoneNumber()
    {
        $spiker = new Spiker();

        $spiker->setPhoneNumber('1234567890');
        $this->assertEquals($spiker->getPhoneNumber(), '11234567890');

        $spiker->setPhoneNumber('11234567890');
        $this->assertEquals($spiker->getPhoneNumber(), '11234567890');

        $spiker->setPhoneNumber('123-456-7890');
        $this->assertEquals($spiker->getPhoneNumber(), '11234567890');

        $spiker->setPhoneNumber('1-(123)-456-7890');
        $this->assertEquals($spiker->getPhoneNumber(), '11234567890');
    }

    public function testGetFullName()
    {
        $spiker = new Spiker();

        $spiker->setFirstName("Test");
        $spiker->setLastName("User");

        $this->assertEquals($spiker->getFullName(), "Test User");
    }
}
