<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240422165631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DB83297E7');
        $this->addSql('ALTER TABLE commande CHANGE reservation_id reservation_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX fk_6eeaa67db83297e7 ON commande');
        $this->addSql('CREATE INDEX IDX_6EEAA67DB83297E7 ON commande (reservation_id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DB83297E7');
        $this->addSql('ALTER TABLE commande CHANGE reservation_id reservation_id INT NOT NULL');
        $this->addSql('DROP INDEX idx_6eeaa67db83297e7 ON commande');
        $this->addSql('CREATE INDEX FK_6EEAA67DB83297E7 ON commande (reservation_id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id)');
    }
}
