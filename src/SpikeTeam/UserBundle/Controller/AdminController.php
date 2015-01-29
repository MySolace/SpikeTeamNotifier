<?php

namespace SpikeTeam\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use SpikeTeam\UserBundle\Entity\Admin;

class AdminController extends Controller
{
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
