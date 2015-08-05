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
     * @Route("/admin", name="admin")
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

            return $this->redirect($this->generateUrl('admin'));
        }

        // send to template
        return $this->render('SpikeTeamUserBundle:Admin:adminsAll.html.twig', array(
            'admins' => $admins,
            'form' => $form->createView(),
        ));
    }

    /**
     * Showing indiv admin user here
     * @Route("/admin/edit/{email}", name="admin_edit")
     */
    public function adminEditAction($email, Request $request)
    {
        $securityContext = $this->get('security.context');
        $currentUser = $securityContext->getToken()->getUser();
        $allUrl = $this->generateUrl('admin');
        if ($currentUser->getEmail() == $email || $securityContext->isGranted('ROLE_SUPER_ADMIN')) {
            $admin = $this->repo->findOneByEmail($email);

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
                    ->add('email', 'email', array(
                        'data' => $admin->getEmail(),
                        'required' => true,
                    ))
                    ->add('password', 'password', array(
                        'required' => false,
                    ))
                    ->add('phoneNumber', 'text', array(
                        'data' => $admin->getPhoneNumber(),
                        'required' => false,
                    ))
                    ->add('isEnabled', 'checkbox', array(
                        'data' => $admin->getIsEnabled(),
                        'label' => 'Opt-in to alert texts?',
                        'required' => false,
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
                    ->add('email', 'email', array(
                        'data' => $admin->getEmail(),
                        'required' => true,
                    ))
                    ->add('password', 'password', array(
                        'required' => false,
                    ))
                    ->add('phoneNumber', 'text', array(
                        'data' => $admin->getPhoneNumber(),
                        'required' => false,
                    ))
                    ->add('isEnabled', 'checkbox', array(
                        'data' => $admin->getIsEnabled(),
                        'label' => 'Opt-in to alert texts?',
                        'required' => false,
                    ))
                    ->add('save', 'submit')
                    ->getForm();
            }

            $existingPassword = $admin->getPassword();

            $form->handleRequest($request);

            if ($form->isValid()) {
                // Only set new password if the password field is filled out
                if ($request->request->get($form->getName())['password'] !== '') {
                    $admin->setPlainPassword($admin->getPassword());
                } else {
                    // else, set password using same password
                    $admin->setPassword($existingPassword);
                }

                // Process number to remove extra characters and add '1' country code
                $processedNumber = $this->get('spike_team.user_helper')
                    ->processNumber($request->request->get($form->getName())['phoneNumber']);
                if ($processedNumber) {
                    $admin->setPhoneNumber($processedNumber);
                }
                if (!$admin->getPhoneNumber()) {
                    $admin->setIsEnabled(false);
                }

                $this->em->persist($admin);
                $this->em->flush();
                return $this->redirect($this->generateUrl('admin'));
            }

            return $this->render('SpikeTeamUserBundle:Admin:adminForm.html.twig', array(
                'admin' => $admin,
                'form' => $form->createView(),
            ));
        } else {    // If not sufficient authorization, go back to see all admins.
            return $this->render($allUrl);
        }
    }

    /**
     * Delete individual admin here
     * @Route("/admin/delete/{email}", name="admin_delete")
     */
    public function adminDeleteAction($email)
    {
        $admin = $this->repo->findOneByEmail($email);
        $this->em->remove($admin);
        $this->em->flush();

        return $this->redirect($this->generateUrl('admin'));
    }

}
