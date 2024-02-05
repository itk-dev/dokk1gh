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
final class Version20201111135939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE code (id INT AUTO_INCREMENT NOT NULL, template_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, aeos_id VARCHAR(255) DEFAULT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, identifier VARCHAR(255) DEFAULT NULL, note LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_77153098137CF50A (aeos_id), INDEX IDX_771530985DA0FB8 (template_id), INDEX IDX_77153098B03A8386 (created_by_id), INDEX IDX_77153098896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guest (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, enabled TINYINT(1) NOT NULL, sent_at DATETIME DEFAULT NULL, activated_at DATETIME DEFAULT NULL, expired_at DATETIME DEFAULT NULL, name VARCHAR(255) NOT NULL, company VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) NOT NULL, phone_country_code VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, time_ranges LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_ACB79A35B03A8386 (created_by_id), INDEX IDX_ACB79A35896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guest_template (guest_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', template_id INT NOT NULL, INDEX IDX_E84AD7FC9A4AA658 (guest_id), INDEX IDX_E84AD7FC5DA0FB8 (template_id), PRIMARY KEY(guest_id, template_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE itkdev_entity_action_log_entry (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, entity_type VARCHAR(255) NOT NULL, entity_id VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, context LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX entity_idx (entity_type, entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE itkdev_log_entry (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, context LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', level SMALLINT NOT NULL, level_name VARCHAR(50) NOT NULL, extra LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE itkdev_setting (name VARCHAR(255) NOT NULL, section VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, form_type VARCHAR(255) DEFAULT NULL, value_string VARCHAR(255) DEFAULT NULL, value_text LONGTEXT DEFAULT NULL, value_datetime DATETIME DEFAULT NULL, value_integer INT DEFAULT NULL, value_boolean TINYINT(1) DEFAULT NULL, value_array LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE template (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, enabled TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, level VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, aeos_id VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_97601F83B03A8386 (created_by_id), INDEX IDX_97601F83896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, password VARCHAR(255) NOT NULL, last_logged_in_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', gdpr_accepted_at DATETIME DEFAULT NULL, aeos_id VARCHAR(255) DEFAULT NULL, api_key VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649C912ED9D (api_key), INDEX IDX_8D93D649B03A8386 (created_by_id), INDEX IDX_8D93D649896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_template (user_id INT NOT NULL, template_id INT NOT NULL, INDEX IDX_77EDFB83A76ED395 (user_id), INDEX IDX_77EDFB835DA0FB8 (template_id), PRIMARY KEY(user_id, template_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE code ADD CONSTRAINT FK_771530985DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id)');
        $this->addSql('ALTER TABLE code ADD CONSTRAINT FK_77153098B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE code ADD CONSTRAINT FK_77153098896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE guest ADD CONSTRAINT FK_ACB79A35B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE guest ADD CONSTRAINT FK_ACB79A35896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE guest_template ADD CONSTRAINT FK_E84AD7FC9A4AA658 FOREIGN KEY (guest_id) REFERENCES guest (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guest_template ADD CONSTRAINT FK_E84AD7FC5DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE template ADD CONSTRAINT FK_97601F83B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE template ADD CONSTRAINT FK_97601F83896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_template ADD CONSTRAINT FK_77EDFB83A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_template ADD CONSTRAINT FK_77EDFB835DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guest_template DROP FOREIGN KEY FK_E84AD7FC9A4AA658');
        $this->addSql('ALTER TABLE code DROP FOREIGN KEY FK_771530985DA0FB8');
        $this->addSql('ALTER TABLE guest_template DROP FOREIGN KEY FK_E84AD7FC5DA0FB8');
        $this->addSql('ALTER TABLE user_template DROP FOREIGN KEY FK_77EDFB835DA0FB8');
        $this->addSql('ALTER TABLE code DROP FOREIGN KEY FK_77153098B03A8386');
        $this->addSql('ALTER TABLE code DROP FOREIGN KEY FK_77153098896DBBDE');
        $this->addSql('ALTER TABLE guest DROP FOREIGN KEY FK_ACB79A35B03A8386');
        $this->addSql('ALTER TABLE guest DROP FOREIGN KEY FK_ACB79A35896DBBDE');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE template DROP FOREIGN KEY FK_97601F83B03A8386');
        $this->addSql('ALTER TABLE template DROP FOREIGN KEY FK_97601F83896DBBDE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B03A8386');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649896DBBDE');
        $this->addSql('ALTER TABLE user_template DROP FOREIGN KEY FK_77EDFB83A76ED395');
        $this->addSql('DROP TABLE code');
        $this->addSql('DROP TABLE guest');
        $this->addSql('DROP TABLE guest_template');
        $this->addSql('DROP TABLE itkdev_entity_action_log_entry');
        $this->addSql('DROP TABLE itkdev_log_entry');
        $this->addSql('DROP TABLE itkdev_setting');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE template');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_template');
    }
}
