<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260829120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute une contrainte UNIQUE(user_id, discipline_id) sur discipline_tracking pour empecher les doublons';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DISCIPLINE_TRACKING_USER_DISCIPLINE ON discipline_tracking (user_id, discipline_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_DISCIPLINE_TRACKING_USER_DISCIPLINE');
    }
}
