<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180312132140 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE itkdev_log_entry');
        $this->addSql('ALTER TABLE fos_user ADD gdpr_accepted_at DATETIME DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE itkdev_log_entry (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, message LONGTEXT NOT NULL COLLATE utf8_unicode_ci, context LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', level SMALLINT NOT NULL, level_name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, extra LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fos_user DROP gdpr_accepted_at');
    }
}
