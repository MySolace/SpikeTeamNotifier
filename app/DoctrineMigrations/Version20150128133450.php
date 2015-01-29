<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use SpikeTeam\SettingBundle\Entity\Setting;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150128133450 extends AbstractMigration implements ContainerAwareInterface
{

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $em = $this->container->get('doctrine.orm.entity_manager');
        $settingsToSet = array(
            'alert_timeout' => '24 hours',
            'twilio_sid' => 'Your Twilio SID',
            'twilio_token' => 'Your Twilio token',
            'twilio_number' => 'Your Twilio number',
            'twilio_message' => 'Default message',
            'twilio_response' => 'Default response',
            'token_usage' => 'Use this username-token pair to generate your X-WSSE request headers, as per the instructions at http://bit.ly/1uBiS5z if you\'d like to use the API.'
        );

        foreach ($settingsToSet as $key => $value) {
            $setting = new Setting();
            $setting->setName($key);
            $setting->setSetting($value);
            $em->persist($setting);
            $em->flush();
        }

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $em = $this->container->get('doctrine.orm.entity_manager');
        $settingsToErase = array(
            'alert_timeout',
            'twilio_sid',
            'twilio_token',
            'twilio_number',
            'twilio_message',
            'twilio_response',
            'token_usage'
        );

        foreach ($settingsToErase as $name) {
            $setting = $em->getRepository('SpikeTeamSettingBundle:Setting')->findOneByName($name);
            $em->remove($setting);
            $em->flush();
        }

    }
}
