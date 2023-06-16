<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230616114241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe_status ADD recipe_id INT NOT NULL');
        $this->addSql('ALTER TABLE recipe_status ADD CONSTRAINT FK_F61E7B3D59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id)');
        $this->addSql('CREATE INDEX IDX_F61E7B3D59D8A214 ON recipe_status (recipe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe_status DROP FOREIGN KEY FK_F61E7B3D59D8A214');
        $this->addSql('DROP INDEX IDX_F61E7B3D59D8A214 ON recipe_status');
        $this->addSql('ALTER TABLE recipe_status DROP recipe_id');
    }
}
