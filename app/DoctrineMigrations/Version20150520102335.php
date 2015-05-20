<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use SpikeTeam\UserBundle\Entity\Admin;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150520102335 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $admins = $this->container->get('doctrine.orm.entity_manager')
                    ->getRepository('SpikeTeamUserBundle:Admin')->findAll();
        $userManager = $this->container->get('fos_user.user_manager');

        foreach($admins as $admin) {
            $admin->setPlainPassword($admin->getLastName().'!');
            $userManager->updateUser($admin, true);
        }
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $admins = $this->container->get('doctrine.orm.entity_manager')
                    ->getRepository('SpikeTeamUserBundle:Admin')->findAll();
        $userManager = $this->container->get('fos_user.user_manager');

        foreach($admins as $admin) {
            $admin->setPlainPassword(' ');
            $userManager->updateUser($admin, true);
        }
    }
}
