<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260829180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Relie Activity a Quest (au lieu de DisciplineTracking) pour savoir precisement quelle quete/malus a ete validee, et interdit deux validations de la meme quete le meme jour';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE activity_new (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date DATETIME NOT NULL, exp_won INTEGER NOT NULL, quest_id INTEGER NOT NULL, CONSTRAINT FK_ACTIVITY_QUEST FOREIGN KEY (quest_id) REFERENCES quest (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('DROP TABLE activity');
        $this->addSql('ALTER TABLE activity_new RENAME TO activity');
        $this->addSql('CREATE INDEX IDX_ACTIVITY_QUEST ON activity (quest_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ACTIVITY_QUEST_DATE ON activity (quest_id, date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE activity_new (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date DATETIME NOT NULL, exp_won INTEGER NOT NULL, discipline_tracking_id INTEGER NOT NULL, CONSTRAINT FK_AC74095A1A4D0584 FOREIGN KEY (discipline_tracking_id) REFERENCES discipline_tracking (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('DROP TABLE activity');
        $this->addSql('ALTER TABLE activity_new RENAME TO activity');
        $this->addSql('CREATE INDEX IDX_ACTIVITY_DISCIPLINE_TRACKING ON activity (discipline_tracking_id)');
    }
}
