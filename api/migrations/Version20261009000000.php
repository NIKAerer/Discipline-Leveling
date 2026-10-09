<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261009000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renomme les index de lol_match et activity avec les noms générés par Doctrine, pour que doctrine:schema:validate passe';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_LOL_MATCH_DISCIPLINE_TRACKING');
        $this->addSql('CREATE INDEX IDX_58227AA21A4D0584 ON lol_match (discipline_tracking_id)');
        $this->addSql('DROP INDEX IDX_ACTIVITY_QUEST');
        $this->addSql('CREATE INDEX IDX_AC74095A209E9EF4 ON activity (quest_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_58227AA21A4D0584');
        $this->addSql('CREATE INDEX IDX_LOL_MATCH_DISCIPLINE_TRACKING ON lol_match (discipline_tracking_id)');
        $this->addSql('DROP INDEX IDX_AC74095A209E9EF4');
        $this->addSql('CREATE INDEX IDX_ACTIVITY_QUEST ON activity (quest_id)');
    }
}
