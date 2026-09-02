<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260830200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refonte de lol_match (lane, matchup, KDA, duree, farm, lp_change au lieu de lp_before/lp_after) et ajout de lp_starting sur discipline_tracking';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lol_match_new (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, played_at DATETIME NOT NULL, champion VARCHAR(100) NOT NULL, role VARCHAR(20) DEFAULT NULL, matchup VARCHAR(100) DEFAULT NULL, kills INTEGER NOT NULL, deaths INTEGER NOT NULL, assists INTEGER NOT NULL, game_duration_minutes INTEGER DEFAULT NULL, cs INTEGER DEFAULT NULL, win BOOLEAN NOT NULL, lp_change INTEGER NOT NULL, discipline_tracking_id INTEGER NOT NULL, CONSTRAINT FK_LOL_MATCH_DISCIPLINE_TRACKING FOREIGN KEY (discipline_tracking_id) REFERENCES discipline_tracking (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('DROP TABLE lol_match');
        $this->addSql('ALTER TABLE lol_match_new RENAME TO lol_match');
        $this->addSql('CREATE INDEX IDX_LOL_MATCH_DISCIPLINE_TRACKING ON lol_match (discipline_tracking_id)');
        $this->addSql('ALTER TABLE discipline_tracking ADD COLUMN lp_starting INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lol_match_new (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, played_at DATETIME NOT NULL, champion VARCHAR(100) NOT NULL, role VARCHAR(20) DEFAULT NULL, win BOOLEAN NOT NULL, lp_before INTEGER NOT NULL, lp_after INTEGER NOT NULL, discipline_tracking_id INTEGER NOT NULL, CONSTRAINT FK_LOL_MATCH_DISCIPLINE_TRACKING FOREIGN KEY (discipline_tracking_id) REFERENCES discipline_tracking (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('DROP TABLE lol_match');
        $this->addSql('ALTER TABLE lol_match_new RENAME TO lol_match');
        $this->addSql('CREATE INDEX IDX_LOL_MATCH_DISCIPLINE_TRACKING ON lol_match (discipline_tracking_id)');
        // lp_starting reste en place sur un rollback (SQLite < 3.35 ne supporte pas DROP COLUMN), sans impact.
    }
}
