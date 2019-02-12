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
class Version20180309144329 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('INSERT INTO itkdev_setting (name, type, form_type, description, value_text) VALUES (:name, :type, :form_type, :description, :value)', [
            'name' => 'admin_gdpr',
            'type' => 'text',
            'form_type' => 'textarea',
            'description' => 'Information on GDPR (terms and conditions)',
            'value' => '<h1>General Data Protection Regulation (GDPR)</h1>

<p><a href="https://www.eugdpr.org/">https://www.eugdpr.org/</a></p>',
        ]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("DELETE FROM itkdev_setting WHERE name = 'admin_gdpr'");
    }
}
