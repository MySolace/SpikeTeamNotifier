<?php

namespace SpikeTeam\ButtonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $group = ($mostRecent == false) ? null : $group = $mostRecent->getGroup();
        $ids = $em->getRepository('SpikeTeamUserBundle:SpikerGroup')->getAllIds();

        $next = 1;
        $next = $this->getNextGroup();

        $canPush = ($this->checkPrevPushes()) ? true : false;
        return $this->render('SpikeTeamButtonBundle:Button:index.html.twig', array(
            'goUrl' => $this->generateUrl('goteamgo', array('gid' => $next)),
            'canPush' => $canPush,
            'mostRecent' => $mostRecent,
            'ids' => $ids,
            'next' => $next
        ));
    }

    /**
     * @Route("/goteamgo/{gid}", name="goteamgo", options={"expose"=true})
     */
    public function goAction($gid)
    {
        $em = $this->getDoctrine()->getManager();
        $newPushTime = $this->checkPrevPushes();
        if ($newPushTime) {
            // Dispatch alert event, to appropriate group if specified
            switch($gid) {
                case 'all':
                    $spikers = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker')->findByEnabledGroup();
                    break;
                default:
                    $group = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:SpikerGroup')->find($gid);
                    $spikers = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker')->findByGroup($group);
                    break;
            }
            $dispatcher = $this->container->get('event_dispatcher');
            $dispatcher->dispatch('alert.send', new AlertEvent($spikers));

            // Record button push
            $push = new ButtonPush($newPushTime, $this->getUser()->getId());
            if (isset($group)) {
                $push->setGroup($group);
            }

            $em->persist($push);
            $em->flush();

            $next = $this->getNextGroup();

            // Send back latest push info
            $id = ($push->getGroup() == null) ? 'All Spikers' : 'Group '.$push->getGroup()->getId();
            return new JsonResponse(array(
                'id' => $id,
                'time' => $push->getPushTime()->format('G:i, m/d/y'),
                'next' => $next
            ));
        } else {
            return new Response('No can do!');      
        }
    }

    /**
     * Returns next group to send alert to. Takes into account disabled groups
     * @param integer $id
     * @return integer $next
     */
    private function getNextGroup($id = null)
    {
        $buttonRepo = $this->getDoctrine()->getRepository('SpikeTeamButtonBundle:ButtonPush');
        $current = $buttonRepo->findMostRecent();

        // In case $id is not supplied
        if ($id == null && isset($current) && $current->getGroup() != null) {
            $id = $current->getGroup()->getId();
        }

        // Advancing to the next group only if it exists and is enabled
        if (isset($id) && gettype($id) == 'integer') {
            $next = $id + 1;
            $groupIds = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:SpikerGroup')->getAllIds();
            while (!isset($groupIds[$next]) || $groupIds[$next] == null || !$groupIds[$next]) {
                $next++;
                if ($next > max(array_keys($groupIds)) ) {
                    $next = 1;
                }
            }
        } else {
            // Dealing w/ 'all's. If all, go back one and see if we can do something with it. If not, default = 1
            $previous = $buttonRepo->findMostRecent($current->getId());
            if ($previous->getGroup() != null) {
                return $this->getNextGroup($previous->getGroup()->getId());
            } else {
                $next = 1;
            }
        }
        return $next;
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
