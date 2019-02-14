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
class Version20190213135831 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO itkdev_setting (name, type, form_type, description, value_string) VALUES (:name, :type, :form_type, :description, :value)', [
            'name' => 'app_expired_header',
            'type' => 'string',
            'form_type' => 'text',
            'description' => 'The app expired header',
            'value' => 'App expired',
        ]);

        $this->addSql('INSERT INTO itkdev_setting (name, type, form_type, description, value_text) VALUES (:name, :type, :form_type, :description, :value)', [
            'name' => 'app_expired_content',
            'type' => 'text',
            'form_type' => 'ckeditor',
            'description' => 'The app expired content


Placeholders:

%expired_on%: The date on which the app expired.
%expired_at%: The date and time at which the app expired.
',
            'value' => '<p>This app expired on %expired_on%</p>',
        ]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("DELETE FROM itkdev_setting WHERE name = 'app_expired_header'");
        $this->addSql("DELETE FROM itkdev_setting WHERE name = 'app_expired_content'");
    }
}
