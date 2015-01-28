<?php

namespace SpikeTeam\RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use SpikeTeam\UserBundle\Entity\Spiker;

class SpikerV1Controller extends FOSRestController
{
    /**
     * GET all current Spikers
     * @return array
     * @Rest\View
     * 
     * @ApiDoc(
     *  resource=true,
     *  description="GET all current Spikers",
     * )
     */
    public function getSpikersAllAction()
    {
        $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
        $spikerRepo->setContainer($this->container);
        $spikers = $spikerRepo->findAll();
        return $spikerRepo->generateJsonResponse(200, $spikers);
    }

    /**
     * GET individual Spiker
     * @param $id
     * @return array
     * @Rest\View
     * 
     * @ApiDoc(
     *  description="GET individual Spiker",
     *  requirements={
     *      {
     *           "name"="Phone Number",
     *           "dataType"="string",
     *           "requirement"="\d+",
     *           "description"="The phone number of the Spiker you'd like to GET"
     *       }
     *   },
     * )
     */
    public function getSpikersAction($phoneNumber)
    {
        $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
        $spikerRepo->setContainer($this->container);
        $spiker = $spikerRepo->findOneByPhoneNumber($phoneNumber);
        if (!$spiker) {
            // If no Spiker is found by that ID
            return $spikerRepo->generateJsonResponse(404);
        }
        return $spikerRepo->generateJsonResponse(200, $spiker);
    }

    /**
     * The method for adding Spikers via REST. FOSRestBundle adds that stupid 's' at the end of the URL. No can do.
     * @return Response $response
     * @Rest\View
     * 
     * @ApiDoc(
     *  description="POST new Spiker",
     *  parameters={
     *      {"name"="first_name", "dataType"="string", "required"="false", "description"="First name of Spiker"},
     *      {"name"="last_name", "dataType"="string", "required"="false", "description"="Last name of Spiker"},
     *      {"name"="phone_number", "dataType"="string", "required"="true", "description"="Phone number of Spiker"},
     *      {"name"="(optional) is_enabled", "dataType"="boolean", "required"="false", "description"="If the user is enabled or not"}
     *  },
     * )
     */
    public function postSpikersAddAction()
    {
        $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
        $spikerRepo->setContainer($this->container);

        $spiker = new Spiker();
        $responseRoute = 'spiketeam_user_user_spikershow';

        if ($data = json_decode($this->getRequest()->getContent(), true)) {
            $spiker = $spikerRepo->setSpikerInfo($spiker, $data);
            if ($spiker) {
                return $spikerRepo->generateJsonResponse(201, null, $responseRoute, $spiker->getPhoneNumber());
            } else {
                return $spikerRepo->generateJsonResponse(418);  // This is a joke, replace it eventually
            }
        } else {
            return $spikerRepo->generateJsonResponse(400);
        }
    }

    /**
     * The method for updating Spikers via REST. Request must include: first_name, last_name, and phone_number.
     * @param $id
     * @return Response $response
     * @Rest\View
     *
     * @ApiDoc(
     *  description="PUT new Spiker info",
     *  requirements={
     *      {
     *           "name"="Phone Number",
     *           "dataType"="string",
     *           "requirement"="\d+",
     *           "description"="The phone number of the Spiker you'd like to PUT"
     *       }
     *   },
     *  parameters={
     *      {"name"="first_name", "dataType"="string", "required"="false", "description"="First name of Spiker"},
     *      {"name"="last_name", "dataType"="string", "required"="false", "description"="Last name of Spiker"},
     *      {"name"="phone_number", "dataType"="string", "required"="true", "description"="Phone number of Spiker"},
     *      {"name"="(optional) is_enabled", "dataType"="boolean", "required"="false", "description"="If the user is enabled or not"}
     *  },
     * )
     */
    public function putSpikersEditAction($phoneNumber)
    {
        $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
        $spikerRepo->setContainer($this->container);
        $spiker = $spikerRepo->findOneByPhoneNumber($phoneNumber);

        if (isset($spiker) && $data = json_decode($this->getRequest()->getContent(), true)) {
            $spiker = $spikerRepo->setSpikerInfo($spiker, $data);
            if ($spiker) {
                return $spikerRepo->generateJsonResponse(204);
            } else {
                return $spikerRepo->generateJsonResponse(418);  // This is a joke, replace it eventually
            }
        } else {
            return $spikerRepo->generateJsonResponse(400);
        }
    }

    /**
     * The method for deleting Spikers via REST
     * @param $id
     * @return Response $response
     * @Rest\View
     *
     * @ApiDoc(
     *  description="DELETE existing Spiker",
     *  requirements={
     *      {
     *           "name"="Phone Number",
     *           "dataType"="string",
     *           "requirement"="\d+",
     *           "description"="The phone number of the Spiker you'd like to DELETE"
     *       }
     *   },
     * )
     */
    public function deleteSpikersDeleteAction($phoneNumber)
    {
        $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
        $spikerRepo->setContainer($this->container);
        $spiker = $spikerRepo->findOneByPhoneNumber($phoneNumber);

        if (isset($spiker)) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($spiker);
            $em->flush();
            return $spikerRepo->generateJsonResponse(204);
        } else {
            return $spikerRepo->generateJsonResponse(400);
        }
    }

    /**
     * Responding to any response to texts being sent out via Twilio
     * @return Response $response
     * @Rest\View
     */
    public function incomingTwilioAction()
    {
        $msg = $this->container->getParameter('twilio_response');
        // send to template
        return $this->render('SpikeTeamRestBundle:Twilio:response.xml.twig', array(
            'msg' => $msg,
        ));
    }

}

