<?php
 
namespace SpikeTeam\ApiBundle\Security;
 
use Escape\WSSEAuthenticationBundle\Security\Core\Authentication\Provider\Provider as BaseProvider;
 
class Provider extends BaseProvider
{
        protected function getSalt(\Symfony\Component\Security\Core\User\UserInterface $user)
        {
                return "";
        }
}