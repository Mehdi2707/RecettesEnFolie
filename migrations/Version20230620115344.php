<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230620115344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe_status CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT FK_A369E2B57C362E5B FOREIGN KEY (recipe_status_id) REFERENCES recipe_status (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe_status CHANGE name name VARCHAR(255) DEFAULT \'en attente\' NOT NULL');
        $this->addSql('ALTER TABLE recipes DROP FOREIGN KEY FK_A369E2B57C362E5B');
    }
}
