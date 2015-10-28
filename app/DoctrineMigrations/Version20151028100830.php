<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151028100830 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fos_user CHANGE is_enabled is_enabled TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE spiker ADD is_captain TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE spiker_group ADD captain_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE spiker_group ADD CONSTRAINT FK_9021917C3346729B FOREIGN KEY (captain_id) REFERENCES spiker (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9021917C3346729B ON spiker_group (captain_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fos_user CHANGE is_enabled is_enabled TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE spiker DROP is_captain');
        $this->addSql('ALTER TABLE spiker_group DROP FOREIGN KEY FK_9021917C3346729B');
        $this->addSql('DROP INDEX UNIQ_9021917C3346729B ON spiker_group');
        $this->addSql('ALTER TABLE spiker_group DROP captain_id');
    }
}
