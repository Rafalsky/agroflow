<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306220116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $values = [];
        $dailies = [
            [
                'name' => 'Obchód i Raport Upadków',
                'points' => 25,
                'type' => 'welfare_death',
                'schema' => '{}'
            ]
        ];

        foreach ($dailies as $d) {
            for ($i = 1; $i <= 7; $i++) {
                $values[] = "('{$d['name']}', {$d['points']}, 'URGENT', $i, true, 'Bioasekuracja', true, '{$d['type']}', '{$d['schema']}')";
            }
        }

        $sql = "INSERT INTO task_template (name, points, priority, weekday, recurring, category, active, widget_type, widget_schema) VALUES \n" . implode(",\n", $values);
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
    // this down() migration is auto-generated, please modify it to your needs

    }
}