<?php

namespace SpikeTeam\ButtonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use SpikeTeam\ButtonBundle\Entity\ButtonPush;
use SpikeTeam\ButtonBundle\Event\AlertEvent;

class ButtonController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $mostRecent = $em->getRepository('SpikeTeamButtonBundle:ButtonPush')->findMostRecent();
        $mostRecentGroup = $em->getRepository('SpikeTeamUserBundle:SpikerGroup')->findMostRecentAlerted();
        $ids = $em->getRepository('SpikeTeamUserBundle:SpikerGroup')->getAllIds();
        $next = 1;
        if ($mostRecentGroup && $mostRecentGroup->getId() != null) {
            $next = ($mostRecentGroup->getId() + 1) % count($ids);
        }

        $canPush = ($this->checkPrevPushes()) ? true : false;
        return $this->render('SpikeTeamButtonBundle:Button:index.html.twig', array(
            'goUrl' => $this->generateUrl('spiketeam_button_button_go'),
            'canPush' => $canPush,
            'mostRecent' => $mostRecent,
            'mostRecentGroup' => $mostRecentGroup,
            'ids' => $ids,
            'nextGroup' => $next
        ));
    }

    /**
     * @Route("/goteamgo")
     */
    public function goAction()
    {
        $newPushTime = $this->checkPrevPushes();
        if ($newPushTime) {
            // Dispatch alert event
            $spikers = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker')->findAll();
            $dispatcher = $this->container->get('event_dispatcher');
            $dispatcher->dispatch('alert.send', new AlertEvent($spikers));

            // Record button push
            $push = new ButtonPush($newPushTime, $this->getUser()->getId());
            $em = $this->getDoctrine()->getManager();
            $em->persist($push);
            $em->flush();
            return $this->redirect($this->generateUrl('spiketeam_button_button_index'));
        } else {
            return new Response('No can do!');      
        }
    }

    /**
     * Check if we are good to go. Returns false if not, current DateTime to set as new push if so.
     */
    public function checkPrevPushes()
    {
        $return = false;
        $pushRepo = $this->getDoctrine()->getRepository('SpikeTeamButtonBundle:ButtonPush');
        $pushes = $pushRepo->findAll();
        $now = new \DateTime();

        if ($pushes) {  // If pushes in system, we need to check how recent they were.
            $lastTime = end($pushes)->getPushTime();
            // Get interval from parameters
            $intervalString = $this->getDoctrine()->getManager()
                ->getRepository('SpikeTeamSettingBundle:Setting')->findOneByName('alert_timeout')->getSetting();
            $testInterval = \DateInterval::createFromDateString($intervalString);
            $testTime = $lastTime->add($testInterval);
            if ($now > $testTime) {  // If outside the 24 hour period, return $now
                $return = $now;
            }
        } else {    // If no pushes in system, we are good to go!
            $return = $now;
        }
        return $return;
    }

}
