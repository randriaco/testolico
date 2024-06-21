<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418111522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adjusts the database schema for reservations and commandes relations.';
    }

    public function up(Schema $schema): void
    {
        // Check if the 'reservation_id' column already exists to avoid duplication error
        $tableCommande = $schema->getTable('commande');
        if (!$tableCommande->hasColumn('reservation_id')) {
            $this->addSql('ALTER TABLE commande ADD reservation_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
            $this->addSql('CREATE INDEX IDX_6EEAA67DB83297E7 ON commande (reservation_id)');
        }

        // Removing the 'has_commande' column if it exists
        $tableReservation = $schema->getTable('reservation');
        if ($tableReservation->hasColumn('has_commande')) {
            $this->addSql('ALTER TABLE reservation DROP has_commande');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        if ($schema->getTable('commande')->hasColumn('reservation_id')) {
            $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DB83297E7');
            $this->addSql('DROP INDEX IDX_6EEAA67DB83297E7 ON commande');
            $this->addSql('ALTER TABLE commande DROP reservation_id');
        }

        if (!$schema->getTable('reservation')->hasColumn('has_commande')) {
            $this->addSql('ALTER TABLE reservation ADD has_commande TINYINT(1) DEFAULT 0 NOT NULL');
        }
    }
}
