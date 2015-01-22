<?php

namespace SpikeTeam\RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use SpikeTeam\UserBundle\Entity\Spiker;
use Symfony\Component\HttpFoundation\Response;

class RestController extends Controller
{

    /**
     * @Route("/spikers/add")
     */
    public function inputSpikerAction()
    {
        return new Response('Spiker!');
    }

    /**
     * Respond to API GET Request
     * @return array
     * @View()
     */
    public function getSpikersAction()
    {
        $spikers = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker')->findAll();

        return array('spikers' => $spikers);
    }

    /**
     * @param Spiker $spiker
     * @return array
     * @View()
     * @ParamConverter("spiker", class="SpikeTeamRestBundle:Spiker")
     */
    public function getSpikerAction(Spiker $spiker)
    {
        return array('spiker' => $spiker);
    }
}
