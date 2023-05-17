<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230517155843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE difficulty_level (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recipes ADD difficulty_level_id INT NOT NULL, DROP difficulty_level');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT FK_A369E2B564890943 FOREIGN KEY (difficulty_level_id) REFERENCES difficulty_level (id)');
        $this->addSql('CREATE INDEX IDX_A369E2B564890943 ON recipes (difficulty_level_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipes DROP FOREIGN KEY FK_A369E2B564890943');
        $this->addSql('DROP TABLE difficulty_level');
        $this->addSql('DROP INDEX IDX_A369E2B564890943 ON recipes');
        $this->addSql('ALTER TABLE recipes ADD difficulty_level VARCHAR(50) NOT NULL, DROP difficulty_level_id');
    }
}
