<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160217163543 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE spiker_group SET name = "Sunday" WHERE id = 1');
        $this->addSql('UPDATE spiker_group SET name = "Monday" WHERE id = 2');
        $this->addSql('UPDATE spiker_group SET name = "Tuesday" WHERE id = 3');
        $this->addSql('UPDATE spiker_group SET name = "Wednesday" WHERE id = 4');
        $this->addSql('UPDATE spiker_group SET name = "Thursday" WHERE id = 5');
        $this->addSql('UPDATE spiker_group SET name = "Friday" WHERE id = 6');
        $this->addSql('UPDATE spiker_group SET name = "Saturday" WHERE id = 7');
        $this->addSql('DELETE FROM spiker_group WHERE id = 8');
        $this->addSql('ALTER TABLE spiker ADD notification_preference INT DEFAULT 0 NOT NULL');
        $this->addSql('INSERT INTO setting VALUES (null, "alerts_per_day", "2")');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE spiker_group SET name = "Group 1" WHERE id = 1');
        $this->addSql('UPDATE spiker_group SET name = "Group 2" WHERE id = 2');
        $this->addSql('UPDATE spiker_group SET name = "Group 3" WHERE id = 3');
        $this->addSql('UPDATE spiker_group SET name = "Group 4" WHERE id = 4');
        $this->addSql('UPDATE spiker_group SET name = "Group 5" WHERE id = 5');
        $this->addSql('UPDATE spiker_group SET name = "Group 6" WHERE id = 6');
        $this->addSql('UPDATE spiker_group SET name = "Group 7" WHERE id = 7');
        $this->addSql('ALTER TABLE spiker DROP notification_preference');
        $this->addSql('DELETE FROM setting WHERE name = "alerts_per_day"');
    }
}
