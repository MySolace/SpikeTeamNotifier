<?php

namespace SpikeTeam\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use SpikeTeam\UserBundle\Entity\Spiker;
use SpikeTeam\UserBundle\Form\SpikerType;

class UserController extends Controller
{

    /**
     * Showing all spikers here
     * @Route("/spikers")
     */
    public function spikersAllAction()
    {
        $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
        $spikers = $spikerRepo->findAll();
        // send to template
        return new Response('All the spikers!');
    }

    /**
     * Showing individual spiker here
     * @Route("/spikers/{phoneNumber}", defaults={"phoneNumber" = 0})
     */
    public function spikerShowAction($phoneNumber)
    {
        if ($phoneNumber == 'add') {
            return $this->redirect($this->generateUrl('spiketeam_user_user_spikeraddform'));
        }
        $em = $this->getDoctrine()->getManager();
        $allUrl = $this->generateUrl('spiketeam_user_user_spikersall');
        if (!$phoneNumber) {
            return $this->redirect($allUrl);
        } else {
            $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
            $spiker = $spikerRepo->findOneByPhoneNumber($phoneNumber);
            if (!$spiker) {
                return $this->redirect($allUrl);
            }
            // Send to template
            return new Response('ID ' . $spiker->getFirstName());            
        }
    }

    // NEED TO ADD EDIT FORM PATHWAY

    /**
     * Form for admins to add spikers directly in-site
     * @Route("/spiker/add")
     */
    public function spikerAddFormAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $spiker = new Spiker();

        $form = $this->createFormBuilder($spiker)
            ->add('firstName')
            ->add('lastName')
            ->add('phoneNumber')
            ->add('Add spiker!', 'submit')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            // Process number to remove extra characters and add '1' country code
            $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
            $processedNumber = $spikerRepo->processNumber($spiker->getPhoneNumber());

            // If it's valid, go ahead and save. Otherwise, redirect back to this form.
            if ($processedNumber) {
                $spiker->setPhoneNumber($processedNumber);
                $em->persist($spiker);
                $em->flush();
                return $this->redirect($this->generateUrl(
                    'spiketeam_user_user_spikershow',
                    array('phoneNumber' => $spiker->getPhoneNumber())
                ));
            } else {
                return $this->redirect($this->generateUrl('spiketeam_user_user_spikeraddform'));
            }
        }

        return $this->render('SpikeTeamUserBundle:Spiker:addForm.html.twig', array(
            'form' => $form->createView(),
        ));
    }


}
