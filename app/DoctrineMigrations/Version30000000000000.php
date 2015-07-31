<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use SpikeTeam\SettingBundle\Entity\Setting;
use SpikeTeam\UserBundle\Entity\Admin;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version30000000000000 extends AbstractMigration implements ContainerAwareInterface
{

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $manager = $this->container->get('doctrine.orm.entity_manager');

        // Get rid of the old migration - this is the new one
        $sql = "SELECT version FROM migration_versions WHERE version = :version";
        $params['version'] = 20150128133450;
        $stmt = $manager->getConnection()->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetchAll();
        if (!(!isset($result) || $result == null)) {
            $this->addSql('DELETE FROM migration_versions WHERE version="'.$params['version'].'"');
            return;
        }

        $settingsToSet = array(
            'alert_timeout' => '24 hours',
            'twilio_sid' => 'Your Twilio SID',
            'twilio_token' => 'Your Twilio token',
            'twilio_number' => 'Your Twilio number',
            'twilio_message' => 'Default message',
            'twilio_response' => 'Default response'
        );

        foreach ($settingsToSet as $key => $value) {
            $setting = new Setting();
            $setting->setName($key);
            $setting->setSetting($value);
            $manager->persist($setting);
            $manager->flush();
        }

        $admin = new Admin();
        $admin->setUsername('admin');
        $admin->setFirstName('Admin');
        $admin->setLastName('User');
        $admin->setEmail('admin@sample.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('User');
        $admin->setPlainPassword('admin');
        $admin->setEnabled('true');
        $admin->addRole('ROLE_SUPER_ADMIN');
        $manager->persist($admin);
        $manager->flush();
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $manager = $this->container->get('doctrine.orm.entity_manager');
        $settingsToErase = array(
            'alert_timeout',
            'twilio_sid',
            'twilio_token',
            'twilio_number',
            'twilio_message',
            'twilio_response'
        );

        foreach ($settingsToErase as $name) {
            $setting = $manager->getRepository('SpikeTeamSettingBundle:Setting')->findOneByName($name);
            $manager->remove($setting);
            $manager->flush();
        }

        $admin = $manager->getRepository('SpikeTeamUserBundle:Admin')->findOneByUsername('admin');
        $manager->remove($admin);
        $manager->flush();
    }
}
