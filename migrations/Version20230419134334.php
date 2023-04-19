<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230419134334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F569574A48');
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F59D86650F');
        $this->addSql('DROP INDEX IDX_E46960F59D86650F ON favorites');
        $this->addSql('DROP INDEX IDX_E46960F569574A48 ON favorites');
        $this->addSql('ALTER TABLE favorites ADD recipe_id INT NOT NULL, ADD user_id INT NOT NULL, DROP recipe_id_id, DROP user_id_id');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F559D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id)');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_E46960F559D8A214 ON favorites (recipe_id)');
        $this->addSql('CREATE INDEX IDX_E46960F5A76ED395 ON favorites (user_id)');
        $this->addSql('ALTER TABLE ingredients DROP FOREIGN KEY FK_4B60114F69574A48');
        $this->addSql('DROP INDEX IDX_4B60114F69574A48 ON ingredients');
        $this->addSql('ALTER TABLE ingredients CHANGE recipe_id_id recipe_id INT NOT NULL');
        $this->addSql('ALTER TABLE ingredients ADD CONSTRAINT FK_4B60114F59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id)');
        $this->addSql('CREATE INDEX IDX_4B60114F59D8A214 ON ingredients (recipe_id)');
        $this->addSql('ALTER TABLE recipes DROP FOREIGN KEY FK_A369E2B59D86650F');
        $this->addSql('DROP INDEX IDX_A369E2B59D86650F ON recipes');
        $this->addSql('ALTER TABLE recipes CHANGE user_id_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT FK_A369E2B5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_A369E2B5A76ED395 ON recipes (user_id)');
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A7269574A48');
        $this->addSql('DROP INDEX IDX_34220A7269574A48 ON steps');
        $this->addSql('ALTER TABLE steps CHANGE recipe_id_id recipe_id INT NOT NULL');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A7259D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id)');
        $this->addSql('CREATE INDEX IDX_34220A7259D8A214 ON steps (recipe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipes DROP FOREIGN KEY FK_A369E2B5A76ED395');
        $this->addSql('DROP INDEX IDX_A369E2B5A76ED395 ON recipes');
        $this->addSql('ALTER TABLE recipes CHANGE user_id user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT FK_A369E2B59D86650F FOREIGN KEY (user_id_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A369E2B59D86650F ON recipes (user_id_id)');
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F559D8A214');
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F5A76ED395');
        $this->addSql('DROP INDEX IDX_E46960F559D8A214 ON favorites');
        $this->addSql('DROP INDEX IDX_E46960F5A76ED395 ON favorites');
        $this->addSql('ALTER TABLE favorites ADD recipe_id_id INT NOT NULL, ADD user_id_id INT NOT NULL, DROP recipe_id, DROP user_id');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F569574A48 FOREIGN KEY (recipe_id_id) REFERENCES recipes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F59D86650F FOREIGN KEY (user_id_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_E46960F59D86650F ON favorites (user_id_id)');
        $this->addSql('CREATE INDEX IDX_E46960F569574A48 ON favorites (recipe_id_id)');
        $this->addSql('ALTER TABLE ingredients DROP FOREIGN KEY FK_4B60114F59D8A214');
        $this->addSql('DROP INDEX IDX_4B60114F59D8A214 ON ingredients');
        $this->addSql('ALTER TABLE ingredients CHANGE recipe_id recipe_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE ingredients ADD CONSTRAINT FK_4B60114F69574A48 FOREIGN KEY (recipe_id_id) REFERENCES recipes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4B60114F69574A48 ON ingredients (recipe_id_id)');
        $this->addSql('ALTER TABLE steps DROP FOREIGN KEY FK_34220A7259D8A214');
        $this->addSql('DROP INDEX IDX_34220A7259D8A214 ON steps');
        $this->addSql('ALTER TABLE steps CHANGE recipe_id recipe_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE steps ADD CONSTRAINT FK_34220A7269574A48 FOREIGN KEY (recipe_id_id) REFERENCES recipes (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_34220A7269574A48 ON steps (recipe_id_id)');
    }
}
