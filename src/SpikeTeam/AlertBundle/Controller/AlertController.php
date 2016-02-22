<?php

namespace SpikeTeam\AlertBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Services_Twilio_Twiml;

use SpikeTeam\AlertBundle\Services\NotificationService;

/**
 * Spiker controller.
 *
 * @Route("/alert")
 */
class AlertController extends Controller
{
    /**
     * @Route("/message", name="alert_message")
     *
     * This controller route is responsible for outputting Twilio readable XML
     * to play whatever is in the Message query parameter.
     */
    public function alertMessageAction(Request $request)
    {
        $apikey = $this->container->getParameter('api_key');

        if ( $request->get('api_key') != $apikey ) {
            throw new AccessDeniedException();
        }

        $response = new Services_Twilio_Twiml();

        $message = $request->get('Message');
        $response->say($message, array('loop' => 3));

        $response = new Response($response->__toString());
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Route("/callback", name="alert_callback")
     *
     * This controller route is hit at the end of a Twilio call attempt with the
     * results of the call.  In particular we care about CallStatus and
     * AnsweredBy.  CallStatus lets us know if the call failed, wasn't answered
     * or was busy.  If the call was completed, AnsweredBy lets us know if it
     * was a human or a voicemail (machine) that handled the call.
     */
    public function alertCallbackAction(Request $request)
    {
        $apikey = $this->container->getParameter('api_key');

        if ( $request->get('api_key') != $apikey ) {
            throw new AccessDeniedException();
        }

        $callStatus = $request->get('CallStatus');
        $answeredBy = $request->get('AnsweredBy');

        //return response if call result was anything other than no-answer, busy or failed
        if (!in_array($callStatus, array('no-answer', 'busy', 'failed')) &&
            $answeredBy != 'machine')  {
            return new JsonResponse(array('result' => 'completed call'));
        }

        //strip + character from twilio formatted number
        $toNumber = substr($request->get('To'), 1);

        $this->get('spiketeam.notification_service')->sendMessage($toNumber);

        return new JsonResponse(array('result' => 'sent text message'));
    }
}
