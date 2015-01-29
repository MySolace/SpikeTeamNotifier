<?php

namespace SpikeTeam\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use SpikeTeam\UserBundle\Entity\Spiker;

class SpikerController extends Controller
{

    /**
     * Showing all spikers here
     * @Route("/spikers")
     */
    public function spikersAllAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
        $spikers = $spikerRepo->findAll();

        $newSpiker = new Spiker();
        $form = $this->createFormBuilder($newSpiker)
            ->add('firstName')
            ->add('lastName')
            ->add('phoneNumber')
            ->add('isEnabled', 'hidden', array(
                'data' => true,
            ))
            ->add('Add', 'submit')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            // Process number to remove extra characters and add '1' country code
            $processedNumber = $spikerRepo->processNumber($newSpiker->getPhoneNumber());

            // If it's valid, go ahead, save, and view the Spiker. Otherwise, redirect back to this form.
            if ($processedNumber) {
                $newSpiker->setPhoneNumber($processedNumber);
                $em->persist($newSpiker);
                $em->flush();
            }
            return $this->redirect($this->generateUrl('spiketeam_user_spiker_spikersall'));
        }

        // send to template
        return $this->render('SpikeTeamUserBundle:Spiker:spikersAll.html.twig', array(
            'spikers' => $spikers,
            'form' => $form->createView(),
        ));
    }

    /**
     * Showing individual spiker here
     * @Route("/spikers/{input}/edit")
     */
    public function spikerEditAction($input, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $allUrl = $this->generateUrl('spiketeam_user_spiker_spikersall');
        $editUrl = $this->generateUrl('spiketeam_user_spiker_spikeredit', array('input' => $input));
        $spikerRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');

        $processedNumber = $spikerRepo->processNumber($input);
        if ($processedNumber) {
            $spiker = $spikerRepo->findOneByPhoneNumber($processedNumber);
            // refactor code so this form lines up externally with one above
            $form = $this->createFormBuilder($spiker)
                ->add('firstName', 'text', array('data' => $spiker->getFirstName()))
                ->add('lastName', 'text', array('data' => $spiker->getLastName()))
                ->add('phoneNumber', 'text', array('data' => $spiker->getPhoneNumber()))
                ->add('isEnabled', 'checkbox', array(
                    'data' => $spiker->getIsEnabled(),
                    'required' => false,
                ))
                ->add('Save Spiker!', 'submit')
                ->add('Remove', 'submit')
                ->getForm();
            $form->handleRequest($request);

            if ($form->get('Remove')->isClicked()) {
                return $this->redirect($this->generateUrl('spiketeam_user_spiker_spikerdelete', array('input' => $input)));
            }

            if ($form->isValid()) {
                // Process number to remove extra characters and add '1' country code
                $processedNumber = $spikerRepo->processNumber($spiker->getPhoneNumber());

                // If it's valid, go ahead and save. Otherwise, redirect back to edit page again.
                if ($processedNumber) {
                    $spiker->setPhoneNumber($processedNumber);
                    $em->persist($spiker);
                    $em->flush();
                    return $this->redirect($allUrl);
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
     * Delete individual spiker here
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
        return $this->redirect($this->generateUrl('spiketeam_user_spiker_spikersall'));
    }

}
