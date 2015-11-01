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
                'data' => ($group == null) ? $this->gRepo->findEmptiest() : $this->gRepo->find($group)
            ))
            ->add('firstName', 'text', array('required' => false))
            ->add('lastName', 'text', array('required' => false))
            ->add('phoneNumber', 'text', array('required' => true))
            ->add('isSupervisor', 'checkbox', array('required' => false))
            ->add('isEnabled', 'hidden', array('data' => true))
            ->add('cohort', 'text', array(
                'attr' => array('size' => '1'),
            ))
            ->add('email')
            ->add('Add', 'submit')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            // Process number to remove extra characters and add '1' country code
            $processedNumber = $this->userHelper->processNumber($newSpiker->getPhoneNumber());

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

            $newSpiker->setCohort(intval($newSpiker->getCohort()));
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
            'group_ids' => $this->gRepo->getAllIds(),
            'group' => $group,
            'group_enabled' => $groupEnabled,
            'count' => $count,
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
                    'required' => true,
                    'attr' => $groupEditAttr
                ))
                ->add('isCaptain', 'checkbox', array(
                    'data' => $spiker->getIsCaptain(),
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

    /**
     * CSV Import spikers here
     * @Route("/import", name="spikers_import", options={"expose":true})
     */
    public function spikersImportAction()
    {
        $url = $this->get('config')->get('csv_import_url', '');
        if (!strlen($url)) return new JsonResponse(false);
        $csvData = file_get_contents($url);
        if (!$csvData) return new JsonResponse(false);
        $lines = explode(PHP_EOL, $csvData);
        array_shift($lines);

        $head = array('timestamp', 'name', 'email', 'phone', 'street1', 'street2', 'city', 'state', 'zip');
        $imported = 0;
        foreach ($lines as $line) {
            $values = array_combine($head, str_getcsv($line));
            $values['phone'] = $this->get('spike_team.user_helper')->processNumber($values['phone']);
            if ($values['phone']
                && !$this->repo->checkByPhoneNumber($values['phone'])
                && !$this->repo->checkByEmail($values['email'])
            ){
                $name = explode(' ', ucwords(strtolower(trim($values['name']))));
                $lastNameKey = max(array_keys($name));
                $firstName = $name[0];
                for ($i = 1; $i < $lastNameKey; $i++) {
                    $firstName .= ' '.$name[$i];
                }
                $lastName = ($lastNameKey > 0) ? $name[$lastNameKey] : null;

                $spiker = new Spiker();
                $spiker->setPhoneNumber($values['phone']);
                if (strpos($values['email'], '@')) {
                    $spiker->setEmail($values['email']);
                }
                $spiker->setFirstName($firstName);
                $spiker->setLastName($lastName);
                $spiker->setIsEnabled(true);
                $spiker->setIsSupervisor(false);
                $spiker->setGroup($this->gRepo->findEmptiest());

                $this->em->persist($spiker);
                $this->em->flush();
                $imported++;
            }
        }
        return new JsonResponse($imported);
    }

    /**
     * Shuffle Spikers randomizedly into Groups
     * @Route("/shuffle", name="spikers_shuffle", options={"expose":true})
     */
    public function spikersShuffleAction()
    {
        $spikers = $this->repo->findAllNonCaptain();
        $groupIds = $this->gRepo->getAllIds();
        $limits = json_decode($this->get('config')->get('group_limits', '{"low":60,"high":80}'));
        for ($i = 0; $i < 3; $i++) {
            shuffle($spikers);
        }

        array_walk($spikers, function(&$spiker) {
            $spiker->setGroup();
        });

        $assigned = 0;
        while ($assigned < count($spikers)) {
            $spiker = $spikers[array_rand($spikers)];
            if ($spiker->getGroup() == null) {
                $spiker->setGroup($this->gRepo->findEmptiest());
                $this->em->persist($spiker);
                $this->em->flush();
                $assigned++;
            }
        }

        return new JsonResponse(true);
    }
}
