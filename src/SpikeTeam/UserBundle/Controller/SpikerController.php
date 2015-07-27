<?php

namespace SpikeTeam\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use SpikeTeam\UserBundle\Entity\Spiker;

/**
 * Spiker controller.
 *
 * @Route("/spikers")
 */
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
     * @Route("/", name="spikers")
     */
    public function spikersAllAction(Request $request)
    {
        $spikers = $this->repo->findAll();
        $existing = false;

        $groupRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:SpikerGroup');
        $recentId = $groupRepo->findMostRecentAlerted()->getId();

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
                if (count($this->repo->findByEmail($newSpiker->getEmail()))
                    || count($this->repo->findByPhoneNumber($processedNumber))
                    ) {
                    $existing = true;
                } else {
                    $newSpiker->setPhoneNumber($processedNumber);
                    $this->em->persist($newSpiker);
                    $this->em->flush();
                    return $this->redirect($this->generateUrl('spikers'));
                }
            }
        }

        // send to template
        return $this->render('SpikeTeamUserBundle:Spiker:spikersAll.html.twig', array(
            'spikers' => $spikers,
            'form' => $form->createView(),
            'existing' => $existing,
            'group_ids' => $groupRepo->getAllIds()
        ));
    }

    /**
     * Mass enable/disable Spikers here
     * @Route("/enabler", name="spikers_enable")
     */
    public function spikerEnablerAction(Request $request)
    {
        // AJAX request/fire event here, instead of HTML redirect?
        $spikers = $this->repo->findAll();
        $data = $request->request->all();
        foreach ($spikers as $key => $spiker) {
            if (isset($data[$spiker->getId().'-enabled']) && $data[$spiker->getId().'-enabled'] == '1') {
                $spiker->setIsEnabled(true);
            } else {
                $spiker->setIsEnabled(false);
            }
            $group = $groupRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:SpikerGroup')
                ->find($data[$spiker->getId().'-group']);
            $spiker->setGroup($group);
            $this->em->persist($spiker);
        }
        $this->em->flush();
        return $this->redirect($this->generateUrl('spikers'));
    }

    /**
     * Showing individual spiker here
     * @Route("/{input}/edit", name="spikers_edit")
     */
    public function spikerEditAction($input, Request $request)
    {
        $allUrl = $this->generateUrl('spikers');
        $editUrl = $this->generateUrl('spikers_edit', array('input' => $input));

        $processedNumber = $this->repo->processNumber($input);
        if ($processedNumber) {
            $deleteUrl = $this->generateUrl('spikers_delete', array('input' => $processedNumber));
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
     * @Route("/{input}/delete", name="spikers_delete")
     */
    public function spikerDeleteAction($input)
    {
        $processedNumber = $this->repo->processNumber($input);
        if ($processedNumber) {
            $spiker = $this->repo->findOneByPhoneNumber($input);
            $this->em->remove($spiker);
            $this->em->flush();
        }
        return $this->redirect($this->generateUrl('spikers'));
    }

}
