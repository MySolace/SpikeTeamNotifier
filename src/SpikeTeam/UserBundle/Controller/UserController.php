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
        $repository = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
        $spikers = $repository->findAll();
        // send to template
        return new Response('All the spikers!');
    }

    /**
     * Showing individual spiker here
     * @Route("/spikers/{id}", defaults={"id" = 0})
     */
    public function spikerShowAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $allUrl = $this->generateUrl('spiketeam_user_user_spikersall');
        if (!$id) {
            return $this->redirect($allUrl);
        } else {
            $repository = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
            $spiker = $repository->find($id);
            if (!$spiker) {
                return $this->redirect($allUrl);
            }
            // Send to template
            return new Response('ID ' . $spiker->getFirstName());            
        }
    }

    /**
     * Form for admins to add spikers directly in-site
     * @Route("/spikers/add")
     */
    public function spikerAddFormAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $spiker = new Spiker();

        $form = $this->createForm(new SpikerType(), $spiker);
        $form->add('Add spiker!', 'submit');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($spiker);
            $em->flush();
            return $this->redirect($this->generateUrl(
                'spiketeam_user_user_spikershow',
                array('id' => $spiker->getId())
            ));
        }

        return $this->render('SpikeTeamUserBundle:Spiker:addForm.html.twig', array(
            'form' => $form->createView(),
        ));
    }


}
