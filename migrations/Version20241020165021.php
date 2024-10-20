<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241020165021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `mod` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, sha1 VARCHAR(255) NOT NULL, size INT NOT NULL, UNIQUE INDEX UNIQ_17F453485E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE news (id INT AUTO_INCREMENT NOT NULL, title_id INT NOT NULL, description_id INT NOT NULL, layout_id INT NOT NULL, epoch_millis INT NOT NULL, UNIQUE INDEX UNIQ_1DD39950A9F87BD (title_id), UNIQUE INDEX UNIQ_1DD39950D9F966B (description_id), UNIQUE INDEX UNIQ_1DD399508C22AA1A (layout_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE news_layout (id INT AUTO_INCREMENT NOT NULL, text_percentage INT NOT NULL, text_alignment INT NOT NULL, min_height INT NOT NULL, max_width INT NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE news_text (id INT AUTO_INCREMENT NOT NULL, text VARCHAR(20000) NOT NULL, color VARCHAR(255) NOT NULL, size INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(16) NOT NULL, email VARCHAR(180) NOT NULL, last_jwt INT NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_IDENTIFIER_UUID (uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD39950A9F87BD FOREIGN KEY (title_id) REFERENCES news_text (id)');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD39950D9F966B FOREIGN KEY (description_id) REFERENCES news_text (id)');
        $this->addSql('ALTER TABLE news ADD CONSTRAINT FK_1DD399508C22AA1A FOREIGN KEY (layout_id) REFERENCES news_layout (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news DROP FOREIGN KEY FK_1DD39950A9F87BD');
        $this->addSql('ALTER TABLE news DROP FOREIGN KEY FK_1DD39950D9F966B');
        $this->addSql('ALTER TABLE news DROP FOREIGN KEY FK_1DD399508C22AA1A');
        $this->addSql('DROP TABLE `mod`');
        $this->addSql('DROP TABLE news');
        $this->addSql('DROP TABLE news_layout');
        $this->addSql('DROP TABLE news_text');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
