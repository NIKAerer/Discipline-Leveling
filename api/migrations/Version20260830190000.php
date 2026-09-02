<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260830190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la table lol_match (suivi manuel des games LoL) et la colonne lp_goal sur discipline_tracking';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lol_match (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, played_at DATETIME NOT NULL, champion VARCHAR(100) NOT NULL, role VARCHAR(20) DEFAULT NULL, win BOOLEAN NOT NULL, lp_before INTEGER NOT NULL, lp_after INTEGER NOT NULL, discipline_tracking_id INTEGER NOT NULL, CONSTRAINT FK_LOL_MATCH_DISCIPLINE_TRACKING FOREIGN KEY (discipline_tracking_id) REFERENCES discipline_tracking (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_LOL_MATCH_DISCIPLINE_TRACKING ON lol_match (discipline_tracking_id)');
        $this->addSql('ALTER TABLE discipline_tracking ADD COLUMN lp_goal INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE lol_match');
        // SQLite ne supporte le DROP COLUMN qu'a partir de la 3.35 -
        // lp_goal reste en place sur un rollback, sans impact (nullable).
    }
}
