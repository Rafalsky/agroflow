<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215170647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_template ADD default_worker_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task_template ADD CONSTRAINT FK_D7A0F5CFFA429291 FOREIGN KEY (default_worker_id) REFERENCES worker (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_D7A0F5CFFA429291 ON task_template (default_worker_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_template DROP CONSTRAINT FK_D7A0F5CFFA429291');
        $this->addSql('DROP INDEX IDX_D7A0F5CFFA429291');
        $this->addSql('ALTER TABLE task_template DROP default_worker_id');
    }
}
