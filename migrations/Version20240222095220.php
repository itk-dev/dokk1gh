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
final class Version20240222095220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert PHP serialized data to JSON';
    }

    public function up(Schema $schema): void
    {
        // Migrate time ranges from PHP serializes data to JSON.
        // Add a new JSON column.
        $this->addSql('ALTER TABLE guest ADD time_ranges_json JSON DEFAULT \'null\' COMMENT \'(DC2Type:json)\'');
        // Copy and convert data.
        $result = $this->connection->executeQuery('SELECT id, time_ranges FROM guest');
        while ($row = $result->fetchAssociative()) {
            $this->addSql('UPDATE guest SET time_ranges_json = :time_ranges_json WHERE id = :id', [
                'id' => $row['id'],
                'time_ranges_json' => json_encode(unserialize($row['time_ranges'])),
            ]);
        }
        // Replace old serialized column with JSON column
        $this->addSql('ALTER TABLE guest DROP time_ranges, RENAME COLUMN time_ranges_json TO time_ranges');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guest ADD time_ranges_serialized LONGTEXT DEFAULT \'N;\' COMMENT \'(DC2Type:array)\'');
        // Copy and convert data.
        $result = $this->connection->executeQuery('SELECT id, time_ranges FROM guest');
        while ($row = $result->fetchAssociative()) {
            $this->addSql('UPDATE guest SET time_ranges_serialized = :time_ranges_serialized WHERE id = :id', [
                'id' => $row['id'],
                'time_ranges_serialized' => serialize(json_decode($row['time_ranges'], true)),
            ]);
        }
        // Replace old JSON column with serialized column
        $this->addSql('ALTER TABLE guest DROP time_ranges, RENAME COLUMN time_ranges_serialized TO time_ranges');
    }
}
