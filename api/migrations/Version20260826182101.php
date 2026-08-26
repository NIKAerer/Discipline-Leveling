<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260826182101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date DATETIME NOT NULL, exp_won INTEGER NOT NULL, discipline_tracking_id INTEGER NOT NULL, CONSTRAINT FK_AC74095A1A4D0584 FOREIGN KEY (discipline_tracking_id) REFERENCES discipline_tracking (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_AC74095A1A4D0584 ON activity (discipline_tracking_id)');
        $this->addSql('CREATE TABLE discipline (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100) NOT NULL, icon VARCHAR(50) NOT NULL)');
        $this->addSql('CREATE TABLE discipline_tracking (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, goal VARCHAR(255) DEFAULT NULL, exp INTEGER NOT NULL, rank VARCHAR(5) NOT NULL, user_id INTEGER NOT NULL, discipline_id INTEGER NOT NULL, CONSTRAINT FK_CACBB7A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CACBB7A3A5522701 FOREIGN KEY (discipline_id) REFERENCES discipline (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CACBB7A3A76ED395 ON discipline_tracking (user_id)');
        $this->addSql('CREATE INDEX IDX_CACBB7A3A5522701 ON discipline_tracking (discipline_id)');
        $this->addSql('CREATE TABLE quest (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(255) NOT NULL, exp_value INTEGER NOT NULL, discipline_tracking_id INTEGER NOT NULL, CONSTRAINT FK_4317F8171A4D0584 FOREIGN KEY (discipline_tracking_id) REFERENCES discipline_tracking (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_4317F8171A4D0584 ON quest (discipline_tracking_id)');
        $this->addSql('CREATE TABLE quest_template (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(150) NOT NULL, exp_value INTEGER NOT NULL, discipline_id INTEGER NOT NULL, CONSTRAINT FK_763AA026A5522701 FOREIGN KEY (discipline_id) REFERENCES discipline (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_763AA026A5522701 ON quest_template (discipline_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(50) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, rank VARCHAR(5) NOT NULL, exp_total INTEGER NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6495E237E06 ON user (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE activity');
        $this->addSql('DROP TABLE discipline');
        $this->addSql('DROP TABLE discipline_tracking');
        $this->addSql('DROP TABLE quest');
        $this->addSql('DROP TABLE quest_template');
        $this->addSql('DROP TABLE user');
    }
}
