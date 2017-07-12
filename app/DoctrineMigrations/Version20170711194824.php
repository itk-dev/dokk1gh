<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170711194824 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE code ADD aeos_id VARCHAR(255) DEFAULT NULL, CHANGE startTime start_time DATETIME NOT NULL, CHANGE endTime end_time DATETIME NOT NULL, ADD identifier VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_77153098137CF50A ON code (aeos_id)');
        $this->addSql('DROP INDEX UNIQ_97601F837D2EE530 ON template');
        $this->addSql('ALTER TABLE template CHANGE aeosid aeos_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_97601F83137CF50A ON template (aeos_id)');
        $this->addSql('ALTER TABLE fos_user ADD aeos_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A6479137CF50A ON fos_user (aeos_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_77153098137CF50A ON code');
        $this->addSql('ALTER TABLE code CHANGE start_time startTime DATETIME NOT NULL, CHANGE end_time endTime DATETIME NOT NULL, DROP aeos_id, DROP identifier');
        $this->addSql('DROP INDEX UNIQ_957A6479137CF50A ON fos_user');
        $this->addSql('ALTER TABLE fos_user DROP aeos_id');
        $this->addSql('DROP INDEX UNIQ_97601F83137CF50A ON template');
        $this->addSql('ALTER TABLE template CHANGE aeos_id aeosId VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_97601F837D2EE530 ON template (aeosId)');
    }
}
