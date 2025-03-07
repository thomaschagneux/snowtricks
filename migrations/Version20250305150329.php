<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250305150329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE figure ADD featured_media_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE figure ADD CONSTRAINT FK_2F57B37AE2532148 FOREIGN KEY (featured_media_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2F57B37AE2532148 ON figure (featured_media_id)');
        $this->addSql('DROP INDEX `primary` ON media_figure');
        $this->addSql('ALTER TABLE media_figure ADD PRIMARY KEY (figure_id, media_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX `PRIMARY` ON media_figure');
        $this->addSql('ALTER TABLE media_figure ADD PRIMARY KEY (media_id, figure_id)');
        $this->addSql('ALTER TABLE figure DROP FOREIGN KEY FK_2F57B37AE2532148');
        $this->addSql('DROP INDEX UNIQ_2F57B37AE2532148 ON figure');
        $this->addSql('ALTER TABLE figure DROP featured_media_id');
    }
}
