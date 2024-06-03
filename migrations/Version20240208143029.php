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

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240208143029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set category on settings';
    }

    public function up(Schema $schema): void
    {
        $setCategory = fn (string $category, array $names) => $this->addSql(
            'UPDATE `setting` SET `category` = :category WHERE `name` IN (:names)',
            [
                'category' => $category,
                'names' => $names ?: ['👻'],
            ],
            [
                'category' => ParameterType::STRING,
                'names' => ArrayParameterType::STRING,
            ]
        );

        $setCategory('app', [
            'app_about_content',
            'app_about_header',
            'app_about_lead',
            'app_expired_content',
            'app_expired_header',
            'app_terms_content',
            'app_terms_header',
            'app_terms_lead',
        ]);

        $setCategory('admin', [
            'admin_gdpr',
        ]);

        $setCategory('guest', [
            'guest_app_sms_body_template',
            'guest_code_sms_template',
        ]);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
