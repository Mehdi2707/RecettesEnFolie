<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230609143149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consultation_user_recipe ADD CONSTRAINT FK_5A31DC82A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE consultation_user_recipe ADD CONSTRAINT FK_5A31DC8259D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id)');
        $this->addSql('CREATE INDEX IDX_5A31DC82A76ED395 ON consultation_user_recipe (user_id)');
        $this->addSql('CREATE INDEX IDX_5A31DC8259D8A214 ON consultation_user_recipe (recipe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consultation_user_recipe DROP FOREIGN KEY FK_5A31DC82A76ED395');
        $this->addSql('ALTER TABLE consultation_user_recipe DROP FOREIGN KEY FK_5A31DC8259D8A214');
        $this->addSql('DROP INDEX IDX_5A31DC82A76ED395 ON consultation_user_recipe');
        $this->addSql('DROP INDEX IDX_5A31DC8259D8A214 ON consultation_user_recipe');
    }
}
