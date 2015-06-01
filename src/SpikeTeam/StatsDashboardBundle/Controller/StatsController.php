<?php

namespace SpikeTeam\StatsDashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class StatsController extends Controller
{
    /**
     * @Route("/stats/", name="getStats")
     * @Method("GET")
     * @Template()
     */
    public function statsAction()
    {
        return array();
    }

    /**
     * @Route("/stats/", name="postStats")
     * @Method("POST")
     */
    public function updateStats(Request $request)
    {
        $uploadedFile = $request->files->get('stats');
        $file = $uploadedFile->move('../web/', 'stats.csv');

        return array();
    }
}
