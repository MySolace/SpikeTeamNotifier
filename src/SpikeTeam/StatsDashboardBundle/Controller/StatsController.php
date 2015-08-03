<?php

namespace SpikeTeam\StatsDashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class StatsController extends Controller
{
    /**
     * Displays stats
     * @Route("/stats/", name="getStats")
     * @Method("GET")
     * @Template()
     */
    public function statsAction()
    {
        return array();
    }

    /**
     * Accepts latest stats in CSV form if API key is valid
     * @Route("/stats/", name="postStats")
     * @Method("POST")
     */
    public function updateStats(Request $request)
    {
        if ($request->request->get('api_key') === $this->container->getParameter('api_key')) {
            $uploadedFile = $request->files->get('stats');
            $file = $uploadedFile->move($this->get('kernel')
                ->getRootDir().'/data/', 'stats.csv');

            return array();
        } else {
            $response = new Response('Invalid Key', 403);
            return $response; 
        }
        
    }

    /** 
     * Serves stats.csv
     *
     * @Route("/data/stats.csv", name="stats_csv")
     */
    public function csvAction()
    {   
        $headers = array(
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'max-age=0, no-cache, no-store'
        );  

        return new Response(file_get_contents($this->get('kernel')
            ->getRootDir().'/data/stats.csv'), 200, $headers);
    }   
}