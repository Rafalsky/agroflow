<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306212136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $values = [];
        $dailies = [
            ['name' => 'Karmienie Hali A', 'points' => 15, 'type' => 'checklist', 'schema' => '{"items":["Zadanie paszy", "Obstukiwanie rur", "Garnięcie na szybko pod lochami", "Paszowanie", "Pojenie loch", "Zgarnięcie odchodów pod ruszt"]}'],
            ['name' => 'Karmienie Hali B', 'points' => 15, 'type' => 'checklist', 'schema' => '{"items":["Zadanie paszy", "Obstukiwanie rur", "Garnięcie na szybko pod lochami", "Paszowanie", "Pojenie loch", "Zgarnięcie odchodów pod ruszt"]}'],
            ['name' => 'Karmienie Porodówka 1', 'points' => 20, 'type' => 'checklist', 'schema' => '{"items":["Zadanie paszy lochom", "Sprzątnięcie odchodów", "Temperatura", "Sprzątanie pod daszkami", "Posypka plus trociny"]}'],
            ['name' => 'Karmienie Porodówka 2', 'points' => 20, 'type' => 'checklist', 'schema' => '{"items":["Zadanie paszy lochom", "Sprzątnięcie odchodów", "Temperatura", "Sprzątanie pod daszkami", "Posypka plus trociny"]}'],
            ['name' => 'Obchód Tuczarnia 1', 'points' => 10, 'type' => 'checklist', 'schema' => '{"items":["Woda OK", "Wentylator OK", "Karmniki OK", "Dosypanie paszy tucznikom", "Temperatury (19-20)"]}'],
            ['name' => 'Obchód Tuczarnia 2', 'points' => 10, 'type' => 'checklist', 'schema' => '{"items":["Woda OK", "Wentylator OK", "Karmniki OK", "Dosypanie paszy tucznikom", "Temperatury (19-20)"]}'],
            ['name' => 'Obchód Tuczarnia 3', 'points' => 10, 'type' => 'checklist', 'schema' => '{"items":["Woda OK", "Wentylator OK", "Karmniki OK", "Dosypanie paszy tucznikom", "Temperatury (19-20)"]}'],
        ];

        foreach ($dailies as $d) {
            for ($i = 1; $i <= 7; $i++) {
                $values[] = "('{$d['name']}', {$d['points']}, 'NORMAL', $i, true, 'Codzienne', true, '{$d['type']}', '{$d['schema']}')";
            }
        }

        $weeklies = [
            "('Główne krycia', 5, 'NORMAL', 1, true, 'Produkcja', true, null, null)",
            "('Sprzedaż tucznika', 10, 'NORMAL', 1, true, 'Sprzedaż', true, null, null)",
            "('Grupowanie', 5, 'NORMAL', 2, true, 'Produkcja', true, null, null)",
            "('Szczepienia', 10, 'URGENT', 3, true, 'Welfare', true, null, null)",
            "('Odsad', 5, 'NORMAL', 4, true, 'Produkcja', true, null, null)",
            "('Przerzut warchlaka', 10, 'NORMAL', 5, true, 'Produkcja', true, null, null)",
            "('Zasiedlenie porodówki', 10, 'NORMAL', 6, true, 'Produkcja', true, null, null)",
        ];

        $all = array_merge($values, $weeklies);
        $sql = "INSERT INTO task_template (name, points, priority, weekday, recurring, category, active, widget_type, widget_schema) VALUES \n" . implode(",\n", $all);

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
    // this down() migration is auto-generated, please modify it to your needs

    }
}