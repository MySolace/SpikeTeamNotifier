<?php

namespace SpikeTeam\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

use SpikeTeam\UserBundle\Entity\Admin;

class AdminController extends Controller
{

    protected $container;
    protected $em;
    protected $repo;
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $this->getDoctrine()->getManager();
        $this->repo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Admin');
    }

    /**
     * Showing all admin users here
     * @Route("/admin")
     */
    public function adminAllAction(Request $request)
    {
        $admins = $this->repo->findAll();

        $newAdmin = new Admin();
        $form = $this->createFormBuilder($newAdmin)
            ->add('firstName', 'text', array('required' => false))
            ->add('lastName', 'text', array('required' => false))
            ->add('email', 'email', array('required' => true))
            ->add('password', 'password', array('required' => true))
            ->add('Add', 'submit')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $newAdmin->setPlainPassword($newAdmin->getPassword());
            $newAdmin->addRole('ROLE_ADMIN');
            $newAdmin->setEnabled(true);
            $this->em->persist($newAdmin);
            $this->em->flush();

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
     * @Route("/admin/{email}/edit")
     */
    public function adminEditAction($email, Request $request)
    {
        $securityContext = $this->get('security.context');
        $currentUser = $securityContext->getToken()->getUser();
        $allUrl = $this->generateUrl('spiketeam_user_admin_adminall');
        if ($currentUser->getEmail() == $email || $securityContext->isGranted('ROLE_SUPER_ADMIN')) {
            $admin = $this->repo->findOneByEmail($email);
            $deleteUrl = $this->generateUrl('spiketeam_user_admin_admindelete', array('email' => $email));
            $message = $this->em->getRepository('SpikeTeamSettingBundle:Setting')->findOneByName('token_usage')->getSetting();

            if ($securityContext->isGranted('ROLE_SUPER_ADMIN')) {
                $form = $this->createFormBuilder($admin)
                    ->add('firstName', 'text', array(
                        'data' => $admin->getFirstName(),
                        'required' => false,
                    ))
                    ->add('lastName', 'text', array(
                        'data' => $admin->getLastName(),
                        'required' => false,
                    ))
                    /*
                    ->add('username', 'text', array(
                        'data' => $admin->getUsername(),
                        'required' => true,
                    ))
                    */
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
                    ->add('firstName', 'text', array(
                        'data' => $admin->getFirstName(),
                        'required' => false,
                    ))
                    ->add('lastName', 'text', array(
                        'data' => $admin->getLastName(),
                        'required' => false,
                    ))
                    /*
                    ->add('username', 'text', array(
                        'data' => $admin->getUsername(),
                        'required' => true,
                    ))
                    */
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
                $this->em->persist($admin);
                $this->em->flush();
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
     * @Route("/admin/{email}/delete")
     */
    public function adminDeleteAction($email)
    {
        $admin = $this->repo->findOneByEmail($email);
        $this->em->remove($admin);
        $this->em->flush();

        return $this->redirect($this->generateUrl('spiketeam_user_admin_adminall'));
    }

}
