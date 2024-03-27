<?php

declare(strict_types=1);

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240129191326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE setting (name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, form_type VARCHAR(255) NOT NULL, value JSON NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Migrate settings.
        $this->addSql('INSERT INTO setting (name, description, type, form_type, value) SELECT name, description, "string", case when form_type = "ckeditor" then "texteditor" else form_type end, JSON_QUOTE(IFNULL(value_string, "")) FROM itkdev_setting WHERE type = "string"');
        $this->addSql('INSERT INTO setting (name, description, type, form_type, value) SELECT name, description, "text", case when form_type = "ckeditor" then "texteditor" else form_type end, JSON_QUOTE(IFNULL(value_text, "")) FROM itkdev_setting WHERE type = "text"');

        $this->addSql('DROP TABLE itkdev_log_entry');

        $this->addSql('DROP TABLE itkdev_setting');

        // Migrate binary UUIDs to real UUIDs.
        $this->addSql('ALTER TABLE guest_template DROP FOREIGN KEY FK_E84AD7FC9A4AA658');

        $this->addSql('ALTER TABLE guest ADD id_binary BINARY(16) DEFAULT NULL COMMENT "(DC2Type:uuid)"');
        $this->addSql('UPDATE guest SET id_binary = UNHEX(REPLACE(id, "-", ""))');
        $this->addSql('ALTER TABLE guest DROP id, RENAME COLUMN id_binary TO id, ADD PRIMARY KEY(id)');

        $this->addSql('ALTER TABLE guest_template DROP PRIMARY KEY');

        $this->addSql('ALTER TABLE guest_template ADD guest_id_binary BINARY(16) DEFAULT NULL COMMENT "(DC2Type:uuid)"');
        $this->addSql('UPDATE guest_template SET guest_id_binary = UNHEX(REPLACE(guest_id, "-", ""))');
        $this->addSql('ALTER TABLE guest_template DROP guest_id, RENAME COLUMN guest_id_binary TO guest_id');

        $this->addSql('ALTER TABLE guest_template ADD PRIMARY KEY(guest_id, template_id)');

        $this->addSql('ALTER TABLE guest_template ADD CONSTRAINT FK_E84AD7FC9A4AA658 FOREIGN KEY (guest_id) REFERENCES guest (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE itkdev_entity_action_log_entry CHANGE context context JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE itkdev_log_entry (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, message LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, context LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', level SMALLINT NOT NULL, level_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, extra LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE itkdev_setting (name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, section VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, form_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, value_string VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, value_text LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, value_datetime DATETIME DEFAULT NULL, value_integer INT DEFAULT NULL, value_boolean TINYINT(1) DEFAULT NULL, value_array JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE setting');
        $this->addSql('ALTER TABLE guest_template CHANGE guest_id guest_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE `user` ADD enabled TINYINT(1) NOT NULL, ADD last_logged_in_at DATETIME DEFAULT NULL, CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE guest CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE itkdev_entity_action_log_entry CHANGE context context JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }
}
