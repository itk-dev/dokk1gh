<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260831101727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guest CHANGE id id BINARY(16) NOT NULL, CHANGE time_ranges time_ranges JSON NOT NULL');
        $this->addSql('ALTER TABLE guest_template CHANGE guest_id guest_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE itkdev_entity_action_log_entry CHANGE context context JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE reset_password_request CHANGE requested_at requested_at DATETIME NOT NULL, CHANGE expires_at expires_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE setting CHANGE value value JSON NOT NULL, CHANGE category category VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE template ADD badge_number_length INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guest CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE time_ranges time_ranges JSON DEFAULT \'null\' COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE guest_template CHANGE guest_id guest_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE itkdev_entity_action_log_entry CHANGE context context JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE reset_password_request CHANGE requested_at requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE expires_at expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE setting CHANGE value value JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE category category VARCHAR(255) DEFAULT \'\'');
        $this->addSql('ALTER TABLE template DROP badge_number_length');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
