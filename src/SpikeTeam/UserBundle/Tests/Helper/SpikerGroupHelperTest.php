<?php

namespace SpikeTeam\UserBundle\Tests\Helper;
use SpikeTeam\UserBundle\Helper\SpikerGroupHelper;

class SpikerGroupHelperTest extends \PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->spikerGroupHelper = new SpikerGroupHelper();
    }

    public function testGetCurrentGroupId()
    {
        //Test Sunday
        $testDate = new \DateTime("2016-02-14 12:00:00");
        $this->assertEquals($this->spikerGroupHelper->getCurrentGroupId($testDate), 1);

        //Test Monday through Saturday
        for ($i = 1; $i < 7; $i++) {
            $testDate = new \DateTime("2016-02-14 12:00:00");
            $testDate->modify("+" . $i . " days");

            $this->assertEquals($this->spikerGroupHelper->getCurrentGroupId($testDate), $i + 1);
        }

        //Test 6 hour difference
        $testDate = new \DateTime("2016-02-14 5:59:59");
        $this->assertEquals($this->spikerGroupHelper->getCurrentGroupId($testDate), 7);
        $testDate = new \DateTime("2016-02-14 6:00:00");
        $this->assertEquals($this->spikerGroupHelper->getCurrentGroupId($testDate), 1);
    }
}
