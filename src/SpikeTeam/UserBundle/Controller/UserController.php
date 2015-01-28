<?php

namespace SpikeTeam\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
        return $this->render('SpikeTeamUserBundle:Spiker:spikersAll.html.twig', array(
            'spikers' => $spikers,
        ));
    }

    /**
     * Showing individual spiker here
     * @Route("/spikers/{input}", defaults={"input" = 0})
     */
    public function spikerShowAction($input, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $allUrl = $this->generateUrl('spiketeam_user_user_spikersall');
        $formUrl = $this->generateUrl('spiketeam_user_user_spikershow', array('input' => 'add'));
        $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');

        if ($input == 'add') {    // If we are adding a spiker here, not viewing one

            $spiker = new Spiker();
            // refactor code so this form lines up externally with one below
            $form = $this->createFormBuilder($spiker)
                ->add('firstName')
                ->add('lastName')
                ->add('phoneNumber')
                ->add('isEnabled', 'hidden', array(
                    'data' => true,
                ))
                ->add('Add spiker!', 'submit')
                ->getForm();
            $form->handleRequest($request);

            if ($form->isValid()) {
                // Process number to remove extra characters and add '1' country code
                $processedNumber = $spikerRepo->processNumber($spiker->getPhoneNumber());

                // If it's valid, go ahead, save, and view the Spiker. Otherwise, redirect back to this form.
                if ($processedNumber) {
                    $spiker->setPhoneNumber($processedNumber);
                    $em->persist($spiker);
                    $em->flush();
                    return $this->redirect($this->generateUrl(
                        'spiketeam_user_user_spikershow',
                        array('input' => $spiker->getPhoneNumber())
                    ));
                } else {
                    return $this->redirect($formUrl);
                }
            }

            return $this->render('SpikeTeamUserBundle:Spiker:form.html.twig', array(
                'form' => $form->createView(),
            ));

        } elseif (!$input) {    // If nothing, show all
            return $this->redirect($allUrl);
        } else {    // Show individual Spiker
            $spiker = $spikerRepo->findOneByPhoneNumber($input);
            if (!$spiker) {
                return $this->redirect($allUrl);
            }
            // Send to template
            return new Response('ID ' . $spiker->getFirstName());            
        }
    }

    /**
     * Showing individual spiker here
     * @Route("/spikers/{input}/edit")
     */
    public function spikerEditAction($input, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $allUrl = $this->generateUrl('spiketeam_user_user_spikersall');
        $editUrl = $this->generateUrl('spiketeam_user_user_spikeredit', array('input' => $input));
        $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');

        $processedNumber = $spikerRepo->processNumber($input);
        if ($processedNumber) {
            $spiker = $spikerRepo->findOneByPhoneNumber($processedNumber);
            // refactor code so this form lines up externally with one above
            $form = $this->createFormBuilder($spiker)
                ->add('firstName', 'text', array('data' => $spiker->getFirstName()))
                ->add('lastName', 'text', array('data' => $spiker->getLastName()))
                ->add('phoneNumber', 'text', array('data' => $spiker->getPhoneNumber()))
                ->add('isEnabled', 'checkbox', array('data' => $spiker->getIsEnabled()))
                ->add('Save Spiker!', 'submit')
                ->getForm();
            $form->handleRequest($request);

            if ($form->isValid()) {
                // Process number to remove extra characters and add '1' country code
                $processedNumber = $spikerRepo->processNumber($spiker->getPhoneNumber());

                // If it's valid, go ahead and save. Otherwise, redirect back to edit page again.
                if ($processedNumber) {
                    $spiker->setPhoneNumber($processedNumber);
                    $em->persist($spiker);
                    $em->flush();
                    return $this->redirect($this->generateUrl(
                        'spiketeam_user_user_spikershow',
                        array('input' => $spiker->getPhoneNumber())
                    ));
                } else {
                    return $this->redirect($editUrl);
                }
            }

            return $this->render('SpikeTeamUserBundle:Spiker:form.html.twig', array(
                'form' => $form->createView(),
            ));
        } else {    // Show individual Spiker
            return $this->redirect($allUrl);
        }
    }

    /**
     * Showing individual spiker here
     * @Route("/spikers/{input}/delete")
     */
    public function spikerDeleteAction($input)
    {
        $em = $this->getDoctrine()->getManager();
        $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');

        $processedNumber = $spikerRepo->processNumber($input);
        if ($processedNumber) {
            $spiker = $spikerRepo->findOneByPhoneNumber($processedNumber);
            $em->remove($spiker);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('spiketeam_user_user_spikersall'));
    }

    /**
     * Showing all admin users here
     * @Route("/admin")
     */
    public function adminAllAction()
    {
        $adminRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Admin');
        $admins = $adminRepo->findAll();
        // send to template
        return $this->render('SpikeTeamUserBundle:Admin:adminAll.html.twig', array(
            'admins' => $admins,
        ));
    }

    /**
     * Showing indiv admin user here
     * @Route("/admin/{username}")
     */
    public function adminShowAction($username)
    {
        $adminRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Admin');
        $admin = $adminRepo->findOneByUsername($username);
        // send to template
        return $this->render('SpikeTeamUserBundle:Admin:adminShow.html.twig', array(
            'admin' => $admin,
        ));
    }

}
