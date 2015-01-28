<?php

namespace SpikeTeam\SettingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use SpikeTeam\SettingBundle\Entity\Setting;

class SettingController extends Controller
{
    /**
     * Showing individual spiker here
     * @Route("/admin/settings/add")
     */
    public function addSettingAction(Request $request)
    {
        $setting = new Setting();
        $form = $this->createFormBuilder($setting)
            ->add('name')
            ->add('setting')
            ->add('Add setting!', 'submit')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($setting);
            $em->flush();
            return $this->redirect($this->generateUrl('spiketeam_setting_setting_addsetting'));
        }

        return $this->render('SpikeTeamUserBundle:Spiker:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
