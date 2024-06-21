<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240110105132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE horaire ADD frequence_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE horaire ADD CONSTRAINT FK_BBC83DB68E487805 FOREIGN KEY (frequence_id) REFERENCES frequence (id)');
        $this->addSql('CREATE INDEX IDX_BBC83DB68E487805 ON horaire (frequence_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE horaire DROP FOREIGN KEY FK_BBC83DB68E487805');
        $this->addSql('DROP INDEX IDX_BBC83DB68E487805 ON horaire');
        $this->addSql('ALTER TABLE horaire DROP frequence_id');
    }
}
