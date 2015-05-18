<?php

namespace SpikeTeam\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use SpikeTeam\UserBundle\Entity\Spiker;

class SpikerController extends Controller
{

    protected $container;
    protected $em;
    protected $repo;
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $this->getDoctrine()->getManager();
        $this->repo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
    }

    /**
     * Showing all spikers here
     * @Route("/spikers")
     */
    public function spikersAllAction(Request $request)
    {
        $spikers = $this->repo->findAll();

        $newSpiker = new Spiker();
        $form = $this->createFormBuilder($newSpiker)
            ->add('firstName', 'text', array('required' => true))
            ->add('lastName', 'text', array('required' => true))
            ->add('phoneNumber', 'text', array('required' => true))
            ->add('isSupervisor', 'checkbox', array('required' => false))
            ->add('isEnabled', 'hidden', array('data' => true))
            ->add('email')
            ->add('Add', 'submit')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            // Process number to remove extra characters and add '1' country code
            $processedNumber = $this->repo->processNumber($newSpiker->getPhoneNumber());

            // If it's valid, go ahead, save, and view the Spiker. Otherwise, redirect back to this form.
            if ($processedNumber) {
                $newSpiker->setPhoneNumber($processedNumber);
                $this->em->persist($newSpiker);
                $this->em->flush();
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
     * Mass enable/disable Spikers here
     * @Route("/spikers/enabler")
     */
    public function spikerEnablerAction(Request $request)
    {
        // AJAX request/fire event here, instead of HTML redirect?
        $spikers = $this->repo->findAll();
        $data = $request->request->all();
        foreach ($spikers as $key => $spiker) {
            if (isset($data[$spiker->getId()]) && $data[$spiker->getId()] == '1') {
                $spiker->setIsEnabled(true);
            } else {
                $spiker->setIsEnabled(false);
            }
            $this->em->persist($spiker);
        }
        $this->em->flush();
        return $this->redirect($this->generateUrl('spiketeam_user_spiker_spikersall'));
    }

    /**
     * Showing individual spiker here
     * @Route("/spikers/{input}/edit")
     */
    public function spikerEditAction($input, Request $request)
    {
        $allUrl = $this->generateUrl('spiketeam_user_spiker_spikersall');
        $editUrl = $this->generateUrl('spiketeam_user_spiker_spikeredit', array('input' => $input));

        $processedNumber = $this->repo->processNumber($input);
        if ($processedNumber) {
            $deleteUrl = $this->generateUrl('spiketeam_user_spiker_spikerdelete', array('input' => $processedNumber));
            $spiker = $this->repo->findOneByPhoneNumber($processedNumber);
            // refactor code so this form lines up externally with one above
            $form = $this->createFormBuilder($spiker)
                ->add('firstName', 'text', array(
                    'data' => $spiker->getFirstName(),
                    'required' => true,
                ))
                ->add('lastName', 'text', array(
                    'data' => $spiker->getLastName(),
                    'required' => true,
                ))
                ->add('phoneNumber', 'text', array(
                    'data' => $spiker->getPhoneNumber(),
                    'required' => true,
                ))
                ->add('email', 'text', array(
                    'data' => $spiker->getEmail(),
                    'required' => false,
                ))
                ->add('isSupervisor', 'checkbox', array(
                    'data' => $spiker->getIsSupervisor(),
                    'required' => false,
                ))
                ->add('isEnabled', 'checkbox', array(
                    'data' => $spiker->getIsEnabled(),
                    'required' => false,
                ))
                ->add('save', 'submit')
                ->getForm();
            $form->handleRequest($request);

            if ($form->isValid()) {
                // Process number to remove extra characters and add '1' country code
                $processedNumber = $this->repo->processNumber($spiker->getPhoneNumber());

                // If it's valid, go ahead and save. Otherwise, redirect back to edit page again.
                if ($processedNumber) {
                    $spiker->setPhoneNumber($processedNumber);
                    $this->em->persist($spiker);
                    $this->em->flush();
                    return $this->redirect($allUrl);
                } else {
                    return $this->redirect($editUrl);
                }
            }

            return $this->render('SpikeTeamUserBundle:Spiker:spikerForm.html.twig', array(
                'spiker' => $spiker,
                'form' => $form->createView(),
                'cancel' => $allUrl,
                'remove' => $deleteUrl
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
        $processedNumber = $this->repo->processNumber($input);
        if ($processedNumber) {
            $spiker = $this->repo->findOneByPhoneNumber($input);
            $this->em->remove($spiker);
            $this->em->flush();
        }
        return $this->redirect($this->generateUrl('spiketeam_user_spiker_spikersall'));
    }

}
