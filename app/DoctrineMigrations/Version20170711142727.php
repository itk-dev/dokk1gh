<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170711142727 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fos_user ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A6479B03A8386 FOREIGN KEY (created_by_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A6479896DBBDE FOREIGN KEY (updated_by_id) REFERENCES fos_user (id)');
        $this->addSql('CREATE INDEX IDX_957A6479B03A8386 ON fos_user (created_by_id)');
        $this->addSql('CREATE INDEX IDX_957A6479896DBBDE ON fos_user (updated_by_id)');
        $this->addSql('ALTER TABLE code ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE code ADD CONSTRAINT FK_77153098B03A8386 FOREIGN KEY (created_by_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE code ADD CONSTRAINT FK_77153098896DBBDE FOREIGN KEY (updated_by_id) REFERENCES fos_user (id)');
        $this->addSql('CREATE INDEX IDX_77153098B03A8386 ON code (created_by_id)');
        $this->addSql('CREATE INDEX IDX_77153098896DBBDE ON code (updated_by_id)');
        $this->addSql('ALTER TABLE template ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE template ADD CONSTRAINT FK_97601F83B03A8386 FOREIGN KEY (created_by_id) REFERENCES fos_user (id)');
        $this->addSql('ALTER TABLE template ADD CONSTRAINT FK_97601F83896DBBDE FOREIGN KEY (updated_by_id) REFERENCES fos_user (id)');
        $this->addSql('CREATE INDEX IDX_97601F83B03A8386 ON template (created_by_id)');
        $this->addSql('CREATE INDEX IDX_97601F83896DBBDE ON template (updated_by_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE code DROP FOREIGN KEY FK_77153098B03A8386');
        $this->addSql('ALTER TABLE code DROP FOREIGN KEY FK_77153098896DBBDE');
        $this->addSql('DROP INDEX IDX_77153098B03A8386 ON code');
        $this->addSql('DROP INDEX IDX_77153098896DBBDE ON code');
        $this->addSql('ALTER TABLE code DROP created_by_id, DROP updated_by_id, DROP deleted_at, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE fos_user DROP FOREIGN KEY FK_957A6479B03A8386');
        $this->addSql('ALTER TABLE fos_user DROP FOREIGN KEY FK_957A6479896DBBDE');
        $this->addSql('DROP INDEX IDX_957A6479B03A8386 ON fos_user');
        $this->addSql('DROP INDEX IDX_957A6479896DBBDE ON fos_user');
        $this->addSql('ALTER TABLE fos_user DROP created_by_id, DROP updated_by_id, DROP deleted_at, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE template DROP FOREIGN KEY FK_97601F83B03A8386');
        $this->addSql('ALTER TABLE template DROP FOREIGN KEY FK_97601F83896DBBDE');
        $this->addSql('DROP INDEX IDX_97601F83B03A8386 ON template');
        $this->addSql('DROP INDEX IDX_97601F83896DBBDE ON template');
        $this->addSql('ALTER TABLE template DROP created_by_id, DROP updated_by_id, DROP deleted_at, DROP created_at, DROP updated_at');
    }
}
