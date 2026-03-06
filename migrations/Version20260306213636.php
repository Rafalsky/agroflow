<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306213636 extends AbstractMigration
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
                'name' => 'Raport Klimatyczny: Porodówki',
                'points' => 15,
                'type' => 'temperature',
                'schema' => '{"zones":[{"id":"porodowka_1","name":"Porodówka 1","min":22,"max":24},{"id":"porodowka_2","name":"Porodówka 2","min":22,"max":24}]}'
            ],
            [
                'name' => 'Raport Klimatyczny: Tuczarnie',
                'points' => 15,
                'type' => 'temperature',
                'schema' => '{"zones":[{"id":"tucz_male","name":"Małe świnki","min":23,"max":24},{"id":"tucz_srednie","name":"Średnie","min":21,"max":22},{"id":"tucz_duze","name":"Duże (Finisher)","min":19,"max":20}]}'
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