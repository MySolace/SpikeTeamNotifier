<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150727144737 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE spiker_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE spiker ADD group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE spiker ADD CONSTRAINT FK_618B2DB9FE54D947 FOREIGN KEY (group_id) REFERENCES spiker_group (id)');
        $this->addSql('CREATE INDEX IDX_618B2DB9FE54D947 ON spiker (group_id)');
        $this->addSql('ALTER TABLE button_push ADD group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE button_push ADD CONSTRAINT FK_7D371E3EFE54D947 FOREIGN KEY (group_id) REFERENCES spiker_group (id)');
        $this->addSql('CREATE INDEX IDX_7D371E3EFE54D947 ON button_push (group_id)');
        $this->addSql('ALTER TABLE fos_user ADD phone_number VARCHAR(11) DEFAULT NULL, ADD is_enabled TINYINT(1) NOT NULL');
        $this->addSql('INSERT into spiker_group VALUES (null, "Group 1"), (null, "Group 2"), (null, "Group 3")');
        $this->addSql('SET foreign_key_checks = 0');
        $this->addSql('UPDATE spiker SET group_id=(MOD(id-1, 3)+1) WHERE id IS NOT NULL');
        $this->addSql('SET foreign_key_checks = 1');
        $this->addSql('DELETE from setting WHERE name="token_usage"');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE spiker DROP FOREIGN KEY FK_618B2DB9FE54D947');
        $this->addSql('ALTER TABLE button_push DROP FOREIGN KEY FK_7D371E3EFE54D947');
        $this->addSql('DROP TABLE spiker_group');
        $this->addSql('DROP INDEX IDX_7D371E3EFE54D947 ON button_push');
        $this->addSql('ALTER TABLE button_push DROP group_id');
        $this->addSql('DROP INDEX IDX_618B2DB9FE54D947 ON spiker');
        $this->addSql('ALTER TABLE spiker DROP group_id');
        $this->addSql('ALTER TABLE fos_user DROP phone_number, DROP is_enabled');
        $this->addSql('INSERT into setting VALUES (null, "token_usage", "Use this username-token pair to generate a X-WSSE header with each request to the API, as per the instructions at http://bit.ly/1uBiS5z. More info about WSSE at http://en.wikipedia.org/wiki/WS-Security, and JavaScript generator at http://www.teria.com/~koseki/tools/wssegen/.")');
    }
}
