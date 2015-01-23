<?php

namespace SpikeTeam\RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use SpikeTeam\UserBundle\Entity\Spiker;

class SpikerV1Controller extends FOSRestController
{

    /**
     * GET all current Spikers
     * @return array
     * @Rest\View()
     * 
     */
    public function getSpikersAllAction()
    {
        $spikers = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker')->findAll();

        return array('spikers' => $spikers);
    }

    /**
     * GET individual Spiker
     * @param $id
     * @return array
     * @Rest\View()
     */
    public function getSpikerAction($id)
    {
        $spiker = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker')->find($id);
        if (!$spiker) {
            throw new NotFoundException('User not found');
        }
        return array('spiker' => $spiker);
    }

    /**
     * The method for adding Spikers via REST
     * @return Response $response
     * @Rest\View()
     */
    public function postSpikersAddAction()
    {
        $em = $this->getDoctrine()->getManager();
        $spiker = new Spiker();
        $responseRoute = 'spiketeam_user_user_spikershow';

        if ($data = json_decode($this->getRequest()->getContent(), true)) {
            $spiker = $this->setSpikerInfo($spiker, $data);
        return $this->generateJsonResponse(201, $responseRoute, $spiker->getId());
        } else {
            return $this->generateJsonResponse(400);
        }
    }

    /**
     * The method for updating Spikers via REST
     * @param $id
     * @return Response $response
     * @Rest\View()
     */
    public function putSpikersEditAction($id)
    {
        $spiker = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker')->find($id);    
        if (isset($spiker) && $data = json_decode($this->getRequest()->getContent(), true)) {
            $spiker = $this->setSpikerInfo($spiker, $data);
            return $this->generateJsonResponse(204);
        } else {
            return $this->generateJsonResponse(400);
        }
    }

    /**
     * The method for deleting Spikers via REST
     * @param $id
     * @return Response $response
     * @Rest\View
     */
    public function deleteSpikersDeleteAction($id)
    {
        $spiker = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker')->find($id);    
        if (isset($spiker)) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($spiker);
            $em->flush();
            return $this->generateJsonResponse(204);
        } else {
            return $this->generateJsonResponse(400);
        }
    }

    /**
     * Common method for setting Spiker info
     * @param Spiker $spiker
     * @param $data
     * @return Spiker $spiker
     */
    public function setSpikerInfo(Spiker $spiker, $data)
    {
        $em = $this->getDoctrine()->getManager();

        $spiker->setFirstName($data['first_name']);
        $spiker->setLastName($data['last_name']);
        $spiker->setPhoneNumber($data['phone_number']);
        $em->persist($spiker);
        $em->flush();

        return $spiker;
    }

    /**
     * Common method for setting Response to send
     * @param $statusCode
     * @param $routeName
     * @param $id
     * @return Response $response
     */
    public function generateJsonResponse($statusCode, $routeName = null, $id = null)
    {
        $response = new Response();
        $response->setStatusCode($statusCode);

        // set the `Location` header only when creating new resources
        if (201 === $statusCode) {
            if (NULL === $routeName) {
                $routeName = 'spiketeam_user_user_spikershow';
            }
            $response->headers->set('Location',
                $this->generateUrl(
                    $routeName, array('id' => $id),
                    true // absolute
                )
            );
        }
        return $response;
    }

}

