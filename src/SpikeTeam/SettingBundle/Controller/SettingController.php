<?php

namespace SpikeTeam\SettingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use SpikeTeam\SettingBundle\Entity\Setting;

class SettingController extends Controller
{

    protected $container;
    protected $em;
    protected $repo;
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $this->getDoctrine()->getManager();
        $this->repo = $this->getDoctrine()->getRepository('SpikeTeamSettingBundle:Setting');
    }

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
            $this->em->persist($setting);
            $this->em->flush();
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
        $settings = $this->repo->findAll();

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
        $allUrl = $this->generateUrl('spiketeam_setting_setting_settingsall');
        $setting = $this->repo->findOneByName($name);

        $form = $this->createFormBuilder($setting)
            ->add('name', 'text', array(
                'data' => $setting->getName(),
                'required' => true,
            ))
            ->add('setting', 'textarea', array(
                'data' => $setting->getSetting(),
                'required' => true,
            ))
            ->add('save', 'submit')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->em->persist($setting);
            $this->em->flush();
            return $this->redirect($allUrl);
        }

        return $this->render('SpikeTeamSettingBundle:Setting:settingForm.html.twig', array(
            'setting' => $setting,
            'form' => $form->createView(),
            'cancel' => $allUrl
        ));
    }

}
