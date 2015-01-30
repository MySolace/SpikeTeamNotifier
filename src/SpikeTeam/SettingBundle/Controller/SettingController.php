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

    /**
     * Showing all settings here
     * @Route("/settings")
     */
    public function settingsAllAction()
    {
        $em = $this->getDoctrine()->getManager();
        $settingRepo = $this->getDoctrine()->getRepository('SpikeTeamSettingBundle:Setting');
        $settings = $settingRepo->findAll();

        // send to template
        return $this->render('SpikeTeamSettingBundle:Setting:settingsAll.html.twig', array(
            'settings' => $settings,
        ));
    }

    /**
     * Showing indiv setting here
     * @Route("/settings/{name}/edit")
     */
    public function settingEditAction($name, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $allUrl = $this->generateUrl('spiketeam_setting_setting_settingsall');
        $settingRepo = $this->getDoctrine()->getRepository('SpikeTeamSettingBundle:Setting');
        $setting = $settingRepo->findOneByName($name);

        $form = $this->createFormBuilder($setting)
            ->add('name', 'text', array(
                'data' => $setting->getName(),
                'required' => true,
            ))
            ->add('setting', 'text', array(
                'data' => $setting->getSetting(),
                'required' => true,
            ))
            ->add('save', 'submit')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($setting);
            $em->flush();
            return $this->redirect($allUrl);
        }

        return $this->render('SpikeTeamSettingBundle:Setting:settingForm.html.twig', array(
            'setting' => $setting,
            'form' => $form->createView(),
            'cancel' => $allUrl
        ));
    }

}
