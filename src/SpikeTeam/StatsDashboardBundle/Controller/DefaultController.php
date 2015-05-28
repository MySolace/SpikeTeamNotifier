<?php

namespace SpikeTeam\StatsDashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/stats/")
     * @Template()
     */
    public function statsAction()
    {
        return array();
    }
}
