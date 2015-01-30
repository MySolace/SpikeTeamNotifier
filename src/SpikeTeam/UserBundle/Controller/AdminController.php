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
    public function adminAllAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $adminRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Admin');
        $admins = $adminRepo->findAll();

        $newAdmin = new Admin();
        $form = $this->createFormBuilder($newAdmin)
            ->add('username', 'text', array('required' => true))
            ->add('email', 'email', array('required' => true))
            ->add('password', 'password', array('required' => true))
            ->add('Add', 'submit')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $newAdmin->setPlainPassword($newAdmin->getPassword());
            $newAdmin->addRole('ROLE_ADMIN');
            $newAdmin->setEnabled(true);
            $em->persist($newAdmin);
            $em->flush();

            return $this->redirect($this->generateUrl('spiketeam_user_admin_adminall'));
        }

        // send to template
        return $this->render('SpikeTeamUserBundle:Admin:adminsAll.html.twig', array(
            'admins' => $admins,
            'form' => $form->createView(),
        ));
    }

    /**
     * Showing indiv admin user here
     * @Route("/admin/{username}/edit")
     */
    public function adminEditAction($username, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $securityContext = $this->get('security.context');
        $currentUser = $securityContext->getToken()->getUser();
        $allUrl = $this->generateUrl('spiketeam_user_admin_adminall');
        if ($currentUser->getUsername() == $username || $securityContext->isGranted('ROLE_SUPER_ADMIN')) {
            $adminRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Admin');
            $admin = $adminRepo->findOneByUsername($username);
            $deleteUrl = $this->generateUrl('spiketeam_user_admin_admindelete', array('username' => $username));
            $message = $em->getRepository('SpikeTeamSettingBundle:Setting')->findOneByName('token_usage')->getSetting();

            if ($securityContext->isGranted('ROLE_SUPER_ADMIN')) {
                $form = $this->createFormBuilder($admin)
                    ->add('username', 'text', array(
                        'data' => $admin->getUsername(),
                        'required' => true,
                    ))
                    ->add('email', 'email', array(
                        'data' => $admin->getEmail(),
                        'required' => true,
                    ))
                    ->add('password', 'password', array(
                        'required' => true,
                    ))
                    ->add('superadmin', 'checkbox', array(      // This shouldn't work this way, but it totally does. WTF.
                        'data' => $admin->hasRole('ROLE_SUPER_ADMIN'),
                        'required' => false,
                    ))
                    ->add('save', 'submit')
                    ->getForm();
            } else {
                $form = $this->createFormBuilder($admin)
                    ->add('username', 'text', array(
                        'data' => $admin->getUsername(),
                        'required' => true,
                    ))
                    ->add('email', 'email', array(
                        'data' => $admin->getEmail(),
                        'required' => true,
                    ))
                    ->add('password', 'password', array(
                        'required' => true,
                    ))
                    ->add('save', 'submit')
                    ->getForm();
            }

            $form->handleRequest($request);

            if ($form->isValid()) {
                $admin->setPlainPassword($admin->getPassword());
                $em->persist($admin);
                $em->flush();
                return $this->redirect($this->generateUrl('spiketeam_user_admin_adminall'));
            }

            return $this->render('SpikeTeamUserBundle:Admin:adminForm.html.twig', array(
                'admin' => $admin,
                'form' => $form->createView(),
                'message' => $message,
                'cancel' => $allUrl,
                'remove' => $deleteUrl
            ));
        } else {    // If not sufficient authorization, go back to see all admins.
            return $this->render($allUrl);
        }
    }

    /**
     * Delete individual admin here
     * @Route("/admin/{username}/delete")
     */
    public function adminDeleteAction($username)
    {
        $em = $this->getDoctrine()->getManager();
        $adminRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Admin');

        $admin = $adminRepo->findOneByUsername($username);
        $em->remove($admin);
        $em->flush();

        return $this->redirect($this->generateUrl('spiketeam_user_admin_adminall'));
    }

}
