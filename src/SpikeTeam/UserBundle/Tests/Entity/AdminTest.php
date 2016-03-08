<?php

namespace SpikeTeam\UserBundle\Tests\Entity;
use SpikeTeam\UserBundle\Entity\Admin;

class AdminTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFriendlyRoleName()
    {

        $captain = new Admin();
        $captain->addRole('ROLE_CAPTAIN');
        $this->assertEquals($captain->getFriendlyRoleName(), 'Captain');

        $admin = new Admin();
        $admin->addRole('ROLE_ADMIN');
        $this->assertEquals($admin->getFriendlyRoleName(), 'Admin');

        $superAdmin = new Admin();
        $superAdmin->addRole('ROLE_SUPER_ADMIN');
        $this->assertEquals($superAdmin->getFriendlyRoleName(), 'Super Admin');
    }
}
