<?php

namespace SpikeTeam\ButtonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\Criteria;

use SpikeTeam\ButtonBundle\Entity\ButtonPush;
use SpikeTeam\ButtonBundle\Event\AlertEvent;
use SpikeTeam\UserBundle\Helper\SpikerGroupHelper;

class ButtonController extends Controller
{
    /**
     * @Route("/", name="button", options={"expose"=true})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $spikerGroupHelper = $this->get('spike_team.spiker_group_helper');
        $securityContext = $this->get('security.context');

        $buttonPushRepo = $em->getRepository('SpikeTeamButtonBundle:ButtonPush');
        $groupRepo = $em->getRepository('SpikeTeamUserBundle:SpikerGroup');
        $settingRepo = $em->getRepository('SpikeTeamSettingBundle:Setting');

        $mostRecent = $buttonPushRepo->findMostRecent();
        $currentGroupId = $spikerGroupHelper->getCurrentGroupId();
        $currentGroup = $groupRepo->find($currentGroupId);

        $maxAlerts = $this->get('config')->get('alerts_per_day', 2);
        $message = $settingRepo->findOneByName('twilio_message')
                      ->getSetting();

        $canPush = $currentGroup->getRecentPushesCount() < $maxAlerts;

        if (!$securityContext->isGranted('ROLE_ADMIN')) {
            $canPush = false;
        }

        $weekdayNames = array(
            1 => 'Sunday',
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday'
        );

        $otherGroups = $groupRepo->findAll();
        $otherNames = array();
        foreach ($otherGroups as $otherGroup) {
            $id = $otherGroup->getId();
            if ($id <= 7) continue;
            $otherNames[$otherGroup->getId()] = $otherGroup->getName();
        }

        return $this->render('SpikeTeamButtonBundle:Button:index.html.twig', array(
            'goUrl'         => $this->generateUrl('goteamgo', array('gid' => $currentGroup)),
            'canPush'       => $canPush,
            'message'       => $message,
            'mostRecent'    => $mostRecent,
            'currentGroup'  => $currentGroup,
            'weekdayNames'  => $weekdayNames,
            'otherNames'    => $otherNames,
        ));
    }

    /**
     * @Route("/goteamgo/{gid}", name="goteamgo", options={"expose"=true})
     */
    public function goAction($gid)
    {
        $em = $this->getDoctrine()->getManager();
        $securityContext = $this->get('security.context');
        $spikerGroupHelper = $this->get('spike_team.spiker_group_helper');
        $sgRepo = $em->getRepository('SpikeTeamUserBundle:SpikerGroup');
        $sRepo = $em->getRepository('SpikeTeamUserBundle:Spiker');
        $config = $this->get('config');

        $pushIsPublic = true;
        $canPush = true;
        if ($gid !== 'all') {
            $group = $sgRepo->find($gid);
            $pushIsPublic = $group->getPublic();
            $perDay = $config->get('alerts_per_day', 5);
            $canPush = $group->getRecentPushesCount() < $perDay;
        }

        if (!$securityContext->isGranted('ROLE_ADMIN')) {
            $canPush = false;
        }

        if ($canPush) {
            // Dispatch alert event, to appropriate group if specified

            switch($gid) {
                case 'all':
                    $spikers = $sRepo->findByEnabledGroup();
                    break;
                default:
                    $spikers = $sRepo->findByGroupDesc($group);
                    break;
            }

            $dispatcher = $this->container->get('event_dispatcher');
            $dispatcher->dispatch('alert.send', new AlertEvent($spikers, $pushIsPublic));

            // Record button push
            $push = new ButtonPush($this->getUser()->getId());
            if (isset($group)) {
                $push->setGroup($group);
            }

            $em->persist($push);
            $em->flush();

            // Send back latest push info
            $id = ($push->getGroup() == null) ? 'All Spikers' : $push->getGroup()->getName() . ' Group';
            return new JsonResponse(array(
                'id' => $id,
                'time' => $push->getPushTime()->format('G:i, m/d/y'),
                'enabled' => $sgRepo->getAllIds()
            ));
        } else {
            return new JsonResponse(false);
        }
    }
}
