<?php

namespace SpikeTeam\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use SpikeTeam\UserBundle\Entity\Spiker;

class ApiV1Controller extends FOSRestController
{
    protected $container;
    protected $em;
    protected $repo;
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $this->getDoctrine()->getManager();
        $this->repo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
    }

    /**
     * GET all current Spikers
     * @return array
     * @Rest\View
     * 
     * @ApiDoc(
     *  resource=true,
     *  description="GET all current Spikers",
     *  requirements={
     *      {
     *           "name"="X-WSSE",
     *           "dataType"="Header",
     *           "requirement"="Your username and token",
     *           "description"="Need to set X-WSSE request header, generated from your token. Check http://bit.ly/1uBiS5z for help on generating X-WSSE headers"
     *       }
     *   },
     * )
     */
    public function getSpikersAllAction(Request $request)
    {
        if (!$this->testForWsse($request)) return $this->generateJsonResponse(401);

        $spikers = $this->repo->findAll();
        return $this->generateJsonResponse(200, $spikers);
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
     *           "name"="X-WSSE",
     *           "dataType"="Header",
     *           "requirement"="Your username and token",
     *           "description"="Need to set X-WSSE request header, generated from your token. Check http://bit.ly/1uBiS5z for help on generating X-WSSE headers"
     *       },
     *      {
     *           "name"="Phone Number",
     *           "dataType"="string",
     *           "requirement"="\d+",
     *           "description"="The phone number of the Spiker you'd like to GET"
     *       }
     *   },
     * )
     */
    public function getSpikersAction(Request $request, $phoneNumber)
    {
        if (!$this->testForWsse($request)) return $this->generateJsonResponse(401);

        $spiker = $this->repo->findOneByPhoneNumber($phoneNumber);
        if (!$spiker) {
            // If no Spiker is found by that ID
            return $this->generateJsonResponse(404);
        }
        return $this->generateJsonResponse(200, $spiker);
    }

    /**
     * The method for adding Spikers via REST. FOSRestBundle adds that stupid 's' at the end of the URL. No can do.
     * @return Response $response
     * @Rest\View
     * 
     * @ApiDoc(
     *  description="POST new Spiker",
     *  requirements={
     *      {
     *           "name"="X-WSSE",
     *           "dataType"="Header",
     *           "requirement"="Your username and token",
     *           "description"="Need to set X-WSSE request header, generated from your token. Check http://bit.ly/1uBiS5z for help on generating X-WSSE headers"
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
    public function postSpikersAddAction(Request $request)
    {
        if (!$this->testForWsse($request)) return $this->generateJsonResponse(401);

        if ($data = json_decode($request->getContent(), true)) {

            // If Spiker exists by that phone number, return error
            $existing = $this->repo->findOneByPhoneNumber($data['phone_number']);
            if ($existing) {
                return $this->generateJsonResponse(400);
            } else {
            // Otherwise, continue on.
                $data['is_enabled'] = true;
                $spiker = new Spiker();
                $spiker = $this->setSpikerInfo($spiker, $data);
                $responseRoute = 'spikers';
                if ($spiker) {
                    return $this->generateJsonResponse(201, null, $responseRoute);
                } else {
                    return $this->generateJsonResponse(418);  // This is a joke, replace it eventually
                }

            }
        } else {
            return $this->generateJsonResponse(400);
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
     *           "name"="X-WSSE",
     *           "dataType"="Header",
     *           "requirement"="Your username and token",
     *           "description"="Need to set X-WSSE request header, generated from your token. Check http://bit.ly/1uBiS5z for help on generating X-WSSE headers"
     *       },
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
    public function putSpikersEditAction(Request $request, $phoneNumber)
    {
        if (!$this->testForWsse($request)) return $this->generateJsonResponse(401);

        $spiker = $this->repo->findOneByPhoneNumber($phoneNumber);

        if (isset($spiker) && $data = json_decode($request->getContent(), true)) {
            $spiker = $this->setSpikerInfo($spiker, $data);
            if ($spiker) {
                return $this->generateJsonResponse(204);
            } else {
                return $this->generateJsonResponse(418);  // This is a joke, replace it eventually
            }
        } else {
            return $this->generateJsonResponse(400);
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
     *           "name"="X-WSSE",
     *           "dataType"="Header",
     *           "requirement"="Your username and token",
     *           "description"="Need to set X-WSSE request header, generated from your token. Check http://bit.ly/1uBiS5z for help on generating X-WSSE headers"
     *       },
     *      {
     *           "name"="Phone Number",
     *           "dataType"="string",
     *           "requirement"="\d+",
     *           "description"="The phone number of the Spiker you'd like to DELETE"
     *       }
     *   },
     * )
     */
    public function deleteSpikersDeleteAction(Request $request, $phoneNumber)
    {
        if (!$this->testForWsse($request)) return $this->generateJsonResponse(401);

        $spiker = $this->repo->findOneByPhoneNumber($phoneNumber);

        if (isset($spiker)) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($spiker);
            $em->flush();
            return $this->generateJsonResponse(204);
        } else {
            return $this->generateJsonResponse(400);
        }
    }

    public function testForWsse($request) {
        return ($request->headers->get('X-WSSE')) ? true : false;
    }

    /**
     * Common method for setting Spiker info. Returns false if phone number can't be formatted correctly.
     * @param Spiker $spiker
     * @param $data
     * @return Spiker $spiker
     */
    private function setSpikerInfo(Spiker $spiker, $data)
    {
        $phoneNumber = $this->get('spike_team.user_helper')->processNumber($data['phone_number']);
        if ($phoneNumber) {
            $em = $this->getManager();
            $spiker->setPhoneNumber($phoneNumber);
            if (isset($data['first_name'])) {
                $spiker->setFirstName($data['first_name']);                
            }
            if (isset($data['last_name'])) {
                $spiker->setLastName($data['last_name']);                
            }
            if (isset($data['is_enabled'])) {
                $spiker->setIsEnabled($data['is_enabled']);
            }
            $em->persist($spiker);
            $em->flush();

            return $spiker;            
        } else {
            return false;
        }
    }

    /**
     * Common method for setting Response to send
     * @param $statusCode
     * @param $routeName
     * @param $phoneNumber
     * @return Response $response
     */
    private function generateJsonResponse($statusCode, $data = null, $routeName = null)
    {
        $response = new Response();
        $response->setStatusCode($statusCode);
        if (isset($data)) {
            $serialized = $this->container->get('serializer')->serialize($data, 'json');
            $response->setContent($serialized);
        } else {
            // set the `Location` header only when creating new resources
            if (201 === $statusCode) {
                if (NULL === $routeName) {
                    $routeName = 'spikers';
                }
                $response->headers->set('Location',
                    $this->container->get('router')->generate(
                        $routeName, array(), true // absolute
                    )
                );
            }
        }

        return $response;
    }

}

