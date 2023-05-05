<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230505120703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962A59D8A214');
        $this->addSql('DROP INDEX IDX_5F9E962A59D8A214 ON comments');
        $this->addSql('ALTER TABLE comments CHANGE recipe_id recipes_id INT NOT NULL');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AFDF2B1FA FOREIGN KEY (recipes_id) REFERENCES recipes (id)');
        $this->addSql('CREATE INDEX IDX_5F9E962AFDF2B1FA ON comments (recipes_id)');
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F559D8A214');
        $this->addSql('DROP INDEX IDX_E46960F559D8A214 ON favorites');
        $this->addSql('ALTER TABLE favorites CHANGE recipe_id recipes_id INT NOT NULL');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5FDF2B1FA FOREIGN KEY (recipes_id) REFERENCES recipes (id)');
        $this->addSql('CREATE INDEX IDX_E46960F5FDF2B1FA ON favorites (recipes_id)');
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A7259D8A214');
        $this->addSql('DROP INDEX IDX_34220A7259D8A214 ON steps');
        $this->addSql('ALTER TABLE steps CHANGE recipe_id recipes_id INT NOT NULL');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A72FDF2B1FA FOREIGN KEY (recipes_id) REFERENCES recipes (id)');
        $this->addSql('CREATE INDEX IDX_34220A72FDF2B1FA ON steps (recipes_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A72FDF2B1FA');
        $this->addSql('DROP INDEX IDX_34220A72FDF2B1FA ON steps');
        $this->addSql('ALTER TABLE steps CHANGE recipes_id recipe_id INT NOT NULL');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A7259D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_34220A7259D8A214 ON steps (recipe_id)');
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5FDF2B1FA');
        $this->addSql('DROP INDEX IDX_E46960F5FDF2B1FA ON favorites');
        $this->addSql('ALTER TABLE favorites CHANGE recipes_id recipe_id INT NOT NULL');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F559D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_E46960F559D8A214 ON favorites (recipe_id)');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AFDF2B1FA');
        $this->addSql('DROP INDEX IDX_5F9E962AFDF2B1FA ON comments');
        $this->addSql('ALTER TABLE comments CHANGE recipes_id recipe_id INT NOT NULL');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5F9E962A59D8A214 ON comments (recipe_id)');
    }
}
