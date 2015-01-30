<?php

namespace SpikeTeam\SettingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TwilioAuxController extends Controller
{

    /**
     * Responding to any response to texts being sent out via Twilio
     * @return Response $response
     * @Route("/twilio/incoming")
     */
    public function incomingTwilioAction()
    {
        $msg = $this->getDoctrine()->getEntityManager()
            ->getRepository('SpikeTeamSettingBundle:Setting')->findOneByName('twilio_response')->getSetting();
        // send to template
        return $this->render('SpikeTeamSettingBundle:Twilio:response.xml.twig', array(
            'msg' => $msg,
        ));
    }

}

