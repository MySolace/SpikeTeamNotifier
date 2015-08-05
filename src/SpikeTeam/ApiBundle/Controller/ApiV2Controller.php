<?php

namespace SpikeTeam\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ApiV2Controller extends Controller
{
    /**
     * Returns JSONResponse of (all) historical Button Pushes
     * @Route("/button/pushes", name="api_v2_get_pushes")
     * @Method("POST")
     */
    public function getPushes(Request $request)
    {
        $pushRepo = $this->getDoctrine()->getRepository('SpikeTeamButtonBundle:ButtonPush');
        if ($request->request->get('api_key') === $this->container->getParameter('api_key')) {
        // if (true) {

            // parse any parameters to specify push being sought
            $param = $request->request->get('param');
            if ($param != null) {
                $param_type = $request->request->get('param_type');
                switch ($param_type) {
                    case 'group':
                        $pushes = $pushRepo->findByGroup($param);
                        break;
                    case 'email':
                        $user = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Admin')
                            ->findOneByEmail($param);
                        $pushes = $pushRepo->findByUserId($user->getId());
                        break;
                    case 'time_before': // Nothing here yet - build this out w/ custom DQL query?
                    case 'time_after':
                    case 'time_between':
                    default:
                        $pushes = $pushRepo->findAll();
                }
                if ($param == 'most_recent') {
                    $pushes = array($pushRepo->findMostRecent());
                }
            } else {
                $pushes = $pushRepo->findAll();
            }

            $return = array();
            foreach ($pushes as $push) {
                $user = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Admin')->find($push->getUserId());
                $return[$push->getId()] = array(
                    'id' => $push->getId(),
                    'time' => $push->getPushTime()->getTimestamp(),
                    'email' => $user->getEmail(),
                    'group' => ($push->getGroup() != null) ? $push->getGroup()->getId() : 'all'
                );
            }

            return new JsonResponse($return);
        } else {
            $response = new Response('Invalid Key', 403);
            return $response; 
        }
        
    }
}