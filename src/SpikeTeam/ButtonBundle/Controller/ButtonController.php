<?php

namespace SpikeTeam\ButtonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use SpikeTeam\ButtonBundle\Entity\ButtonPush;
use SpikeTeam\ButtonBundle\Event\AlertEvent;

class ButtonController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        return array('name' => 'there');
    }

    /**
     * @Route("/goteamgo")
     * @Template()
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
     * @Template()
     */
    public function checkPrevPushes()
    {
        $return = false;
        $pushRepo = $this->getDoctrine()->getRepository('SpikeTeamButtonBundle:ButtonPush');
        $pushes = $pushRepo->findAll();
        $now = new \DateTime();

        if ($pushes) {
            $lastTime = end($pushes)->getPushTime();
            $testInterval = \DateInterval::createFromDateString($this->container->getParameter('alert_wait'));
            $testTime = $lastTime->add($testInterval);
            if ($now > $testTime) {  // If outside the 24 hour period, return $now
                $return = $now;
            }
        } else {
            $return = $now;
        }
        return $return;
    }

}
