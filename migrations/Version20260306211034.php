<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306211034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_instance ADD execution_payload JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE task_template ADD instruction TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE task_template ADD widget_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE task_template ADD widget_schema JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_instance DROP execution_payload');
        $this->addSql('ALTER TABLE task_template DROP instruction');
        $this->addSql('ALTER TABLE task_template DROP widget_type');
        $this->addSql('ALTER TABLE task_template DROP widget_schema');
    }
}
