<?php

namespace SpikeTeam\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

use SpikeTeam\UserBundle\Entity\Spiker;

/**
 * Spiker controller.
 *
 * @Route("/spikers", options={"expose"=true})
 */
class SpikerController extends Controller
{

    protected $container;
    protected $em;
    protected $repo;
    protected $gRepo;
    protected $userHelper;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $this->getDoctrine()->getManager();
        $this->repo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:Spiker');
        $this->gRepo = $this->getDoctrine()->getRepository('SpikeTeamUserBundle:SpikerGroup');
        $this->userHelper = $this->get('spike_team.user_helper');
    }

    /**
     * Showing all spikers here
     * @Route("/{group}", name="spikers", requirements={"group": "\d+"}, options={"expose":true})
     */
    public function spikersAllAction(Request $request, $group = null)
    {
        $spikers = $this->repo->findAll();

        $existing = false;
        $newSpiker = new Spiker();
        $form = $this->createFormBuilder($newSpiker)
            ->add('group', 'entity', array(
                'class' => 'SpikeTeamUserBundle:SpikerGroup',
                'property' => 'name',
                'data' => ($group == null) ? $this->gRepo->findEmptiest() : $this->gRepo->find($group)
            ))
            ->add('firstName', 'text', array('required' => false))
            ->add('lastName', 'text', array('required' => false))
            ->add('phoneNumber', 'text', array(
                'required' => true,
                'error_bubbling' => true
            ))
            ->add('isSupervisor', 'checkbox', array('required' => false))
            ->add('isEnabled', 'hidden', array('data' => true))
            ->add('cohort', 'text', array(
                'attr' => array('size' => '1'),
            ))
            ->add('email', 'text', array(
                'required' => true,
                'error_bubbling' => true
            ))
            ->add('Add', 'submit')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            // Process number to remove extra characters and add '1' country code
            $processedNumber = $this->userHelper->processNumber($newSpiker->getPhoneNumber());

            // If it's valid, go ahead, save, and view the Spiker. Otherwise, redirect back to this form.
            if ($processedNumber) {
                if (count($this->repo->findByPhoneNumber($processedNumber))) {
                    $errors = "ERROR: This phone number is already signed up.";
                } else {
                    $newSpiker->setPhoneNumber($processedNumber);
                    $this->em->persist($newSpiker);
                    $this->em->flush();
                    return $this->redirect($this->generateUrl('spikers'));
                }
            }

            $newSpiker->setCohort(intval($newSpiker->getCohort()));
        } else {
            $errors = $form->getErrors();
        }

        // Sorting by group, then captain, then first name
        usort($spikers, function($a, $b) {
            $aGid = $a->getGroup()->getId();
            $bGid = $b->getGroup()->getId();
            if ($aGid == $bGid) {
                if ($a->getIsCaptain()) {
                    return false;
                } else if ($b->getIsCaptain()) {
                    return true;
                } else {
                    return $a->getFirstName() >= $b->getFirstName();
                }
            } else {
                return $aGid > $bGid;
            }
        });

        $groupEnabled = true;
        if (isset($group)) {
            $groupEnabled = $this->gRepo->find($group)->getEnabled();
            $count = count($this->repo->findByGroup($group));
        } else {
            $count = count($spikers);
        }

        // send to template
        return $this->render('SpikeTeamUserBundle:Spiker:spikersAll.html.twig', array(
            'spikers' => $spikers,
            'form' => $form->createView(),
            'existing' => $existing,
            'groupList' => $this->em->getRepository('SpikeTeamUserBundle:SpikerGroup')->findAll(),
            'group' => $group,
            'group_enabled' => $groupEnabled,
            'count' => $count,
            'errors' => $errors
        ));
    }

    /**
     * Mass enable/disable Spikers and set Groups here
     * @Route("/enabler", name="spikers_enable")
     */
    public function spikerEnablerAction(Request $request)
    {
        // AJAX request/fire event here, instead of HTML redirect?
        $spikers = $this->repo->findAllNonCaptain();
        $data = $request->request->all();
        foreach ($spikers as $spiker) {
            $sid = $spiker->getId();
            if (isset($data[$sid.'-enabled']) && $data[$sid.'-enabled'] == '1') {
                $spiker->setIsEnabled(true);
            } else {
                $spiker->setIsEnabled(false);
            }
            $group = $this->gRepo->find($data[$sid.'-group']);
            $spiker->setGroup($group);
            $this->em->persist($spiker);
        }
        $this->em->flush();

        $returnGroup = (isset($data['group'])) ? $data['group']: null;

        return $this->redirect($this->generateUrl('spikers', array('group' => $returnGroup)));
    }

    /**
     * Spike team signup
     * @Route("/signup", name="spiker_signup")
     */
    public function spikerSignupAction(Request $request)
    {
        $newSpiker = new Spiker();
        $form = $this->createFormBuilder($newSpiker)
            ->add('group', 'entity', array(
                'class' => 'SpikeTeamUserBundle:SpikerGroup',
                'property' => 'name'
            ))
            ->add('firstName', 'text', array('required' => true))
            ->add('lastName', 'text', array('required' => true))
            ->add('phoneNumber', 'text', array(
                'required' => true,
                'error_bubbling' => true
            ))
            ->add('email', 'email', array(
                'required' => true,
                'error_bubbling' => true
            ))
            ->add('notificationPreference', 'choice', array(
                'choices' => array(
                    0 => 'Text',
                    1 => 'Phone Call',
                    2 => 'Both'
                ),
                'required' => true,
                'multiple' => false,
            ))
            ->add('save', 'submit')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            // Process number to remove extra characters and add '1' country code
            $processedNumber = $this->userHelper->processNumber($newSpiker->getPhoneNumber());

            // If it's valid, go ahead, save, and view the Spiker. Otherwise, redirect back to this form.
            if ($processedNumber) {
                if (count($this->repo->findByPhoneNumber($processedNumber))) {
                    $errors = "ERROR: This phone number is already signed up.";
                } else {
                    $newSpiker->setPhoneNumber($processedNumber);
                    $newSpiker->setIsEnabled(true);
                    $newSpiker->setIsSupervisor(false);
                    $this->em->persist($newSpiker);
                    $this->em->flush();
                }
            }
        } else {
            $errors = $form->getErrors();
        }

        // send to template
        return $this->render('SpikeTeamUserBundle:Spiker:signup.html.twig', array(
            'form' => $form->createView(),
            'errors' => $errors
        ));
    }

    /**
     * Showing individual spiker here
     * @Route("/edit/{input}", name="spikers_edit")
     */
    public function spikerEditAction($input, Request $request)
    {
        $allUrl = $this->generateUrl('spikers');
        $editUrl = $this->generateUrl('spikers_edit', array('input' => $input));

        $processedNumber = $this->userHelper->processNumber($input);
        if ($processedNumber) {
            $spiker = $this->repo->findOneByPhoneNumber($processedNumber);
            $oldGroup = $spiker->getGroup();
            $oldIsCaptain = $spiker->getIsCaptain();

            $groupEditAttr = ($spiker->getIsCaptain()) ? array('disabled' => true) : [];
            $form = $this->createFormBuilder($spiker)
                ->add('firstName', 'text', array(
                    'data' => $spiker->getFirstName(),
                    'required' => false,
                ))
                ->add('lastName', 'text', array(
                    'data' => $spiker->getLastName(),
                    'required' => false,
                ))
                ->add('phoneNumber', 'text', array(
                    'data' => $spiker->getPhoneNumber(),
                    'required' => true,
                ))
                ->add('email', 'text', array(
                    'data' => $spiker->getEmail(),
                    'required' => false,
                ))
                ->add('cohort', 'text', array(
                    'data' => $spiker->getCohort(),
                    'required' => false,
                    'attr' => array('size' => '1'),
                ))
                ->add('group', 'entity', array(
                    'class' => 'SpikeTeamUserBundle:SpikerGroup',
                    'property' => 'name',
                    'required' => true,
                    'attr' => $groupEditAttr
                ))
                ->add('isCaptain', 'checkbox', array(
                    'data' => $spiker->getIsCaptain(),
                    'required' => false,
                ))
                ->add('preferredTime', 'choice', array(
                    'choices' => array(
                        'day' => 'Day',
                        'night' => 'Night',
                    ),
                    'required' => false,
                    'multiple' => false,
                    'expanded' => false,
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
                // Deal w/ disabled group select / keep spiker from changing groups if previously captain
                if ($spiker->getGroup() == null
                    || ($oldIsCaptain && $spiker->getGroup() !== $oldGroup)) {
                    $spiker->setGroup($oldGroup);
                }

                if ($spiker->getIsCaptain()) {
                    if (!$spiker->getIsEnabled()) {
                        $spiker->setIsEnabled(true);
                    }
                    $this->em->persist($spiker);
                    $this->get('spike_team.user_helper')->setCaptain($spiker, $spiker->getGroup());
                }

                // Process number to remove extra characters and add '1' country code
                $processedNumber = $this->userHelper->processNumber($spiker->getPhoneNumber());

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
                'form' => $form->createView()
            ));
        } else {    // Show individual Spiker
            return $this->redirect($allUrl);
        }
    }

    /**
     * Delete individual spiker here
     * @Route("/delete/{input}", name="spikers_delete")
     */
    public function spikerDeleteAction($input)
    {
        $processedNumber = $this->userHelper->processNumber($input);
        if ($processedNumber) {
            $spiker = $this->repo->findOneByPhoneNumber($input);
            $this->em->remove($spiker);
            $this->em->flush();
        }
        return $this->redirect($this->generateUrl('spikers'));
    }

    /**
     * CSV Export spikers here
     * @Route("/export/{gid}", name="spikers_export", options={"expose":true})
     *
     * @param int $gid
     */
    public function spikersExportAction($gid = null)
    {
        $response = new StreamedResponse();
        $response->setCallback(function() use ($gid) {

            $handle = fopen('php://output', 'w+');
            fputcsv($handle, array(
                'First Name',
                'Last Name',
                'Phone',
                'Email',
                'Group',
                'Captain?',
                'Cohort',
                'Enabled?',
                'Supervisor?',
            ),',');
            $spikers = $this->repo->findAll();
            foreach ($spikers as $spiker) {
                if ($spiker->getGroup() == $gid || $gid == null) {
                    fputcsv($handle, array(
                        $spiker->getFirstName(),
                        $spiker->getLastName(),
                        $spiker->getPhoneNumber(),
                        $spiker->getEmail(),
                        $spiker->getGroup(),
                        ($spiker->getIsCaptain()) ? 'Yes' : 'No',
                        $spiker->getCohort(),
                        ($spiker->getIsEnabled()) ? 'Yes' : 'No',
                        ($spiker->getIsSupervisor()) ? 'Yes' : 'No',
                    ),',');
                }
            }
            fclose($handle);
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition','attachment; filename="spikers.csv"');

        return $response;
    }
}
