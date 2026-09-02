<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute la colonne avatar (nullable) sur la table user.
 */
final class Version20260828152250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la colonne avatar sur user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD COLUMN avatar VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, name, email, password, rank, exp_total, created_at FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(50) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, rank VARCHAR(5) NOT NULL, exp_total INTEGER NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO user (id, name, email, password, rank, exp_total, created_at) SELECT id, name, email, password, rank, exp_total, created_at FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
    }
}
