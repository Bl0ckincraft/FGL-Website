<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241019133024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE news_text (id INT AUTO_INCREMENT NOT NULL, text VARCHAR(20000) NOT NULL, color VARCHAR(255) NOT NULL, size INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD39950A9F87BD FOREIGN KEY (title_id) REFERENCES news_text (id)');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD39950D9F966B FOREIGN KEY (description_id) REFERENCES news_text (id)');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD399508C22AA1A FOREIGN KEY (layout_id) REFERENCES news_layout (id)');
        $this->addSql('ALTER TABLE news_layout ADD image VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news DROP FOREIGN KEY FK_1DD39950A9F87BD');
        $this->addSql('ALTER TABLE news DROP FOREIGN KEY FK_1DD39950D9F966B');
        $this->addSql('DROP TABLE news_text');
        $this->addSql('ALTER TABLE news DROP FOREIGN KEY FK_1DD399508C22AA1A');
        $this->addSql('ALTER TABLE news_layout DROP image');
    }
}
