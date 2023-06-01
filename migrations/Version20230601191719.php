<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230601191719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Projects and tasks DDL';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE projects (id UUID NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, status VARCHAR(255) NOT NULL, due_date DATE NOT NULL, client VARCHAR(255) DEFAULT NULL, company VARCHAR(255) DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN projects.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN projects.due_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE tasks (id UUID NOT NULL, project_id UUID NOT NULL, description TEXT NOT NULL, is_completed BOOLEAN NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_50586597166D1F9C ON tasks (project_id)');
        $this->addSql('COMMENT ON COLUMN tasks.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tasks.project_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tasks DROP CONSTRAINT FK_50586597166D1F9C');
        $this->addSql('DROP TABLE projects');
        $this->addSql('DROP TABLE tasks');
    }
}
