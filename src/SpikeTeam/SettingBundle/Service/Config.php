<?php

namespace SpikeTeam\SettingBundle\Service;

use SpikeTeam\SettingBundle\Entity\Setting;

/**
 * The app configuration service class. This class persists settings to the
 * database and permits accessing them.
 */
class Config
{
    /**
     * A local copy of the entity manager
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * A stored repository for easy access instead of hitting $em every time.
     *
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repo;

    /**
     * Constructor.
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
        $this->repo = $this->em->getRepository('SpikeTeamSettingBundle:Setting');
    }

    /**
     * Retrieve a setting based on its value. If no value is found in the
     * database, return the default value.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return Setting
     *
     * @throws \RuntimeException
     */
    public function get($name, $default = null)
    {
        $setting = $this->getSetting($name);

        if (null !== $setting) {
            return $setting->getSetting();
        } else if (null !== ($default)) {
            return $this->set($name, $default)->getSetting();
        } else {
            throw new \RuntimeException(sprintf('Setting %s could not be found and no default value set.', $name));
        }
    }

    /**
     * Set a setting.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set($name, $value)
    {
        $setting = $this->getSetting($name);
        if (null === $setting) {
            $setting = new Setting();
            $setting->setName($name);
            $this->em->persist($setting);
        }

        $setting->setSetting($value);
        $this->em->flush($setting);

        return $setting;
    }

    /**
     * Get a setting by name from the DB.
     *
     * @param string $name
     *
     * @return Setting
     */
    private function getSetting($name)
    {
        $setting = $this->repo->findOneByName($name);

        return $setting;
    }
}
