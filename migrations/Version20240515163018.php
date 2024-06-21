<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240515163018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chaise (id INT AUTO_INCREMENT NOT NULL, table_id INT NOT NULL, reservee TINYINT(1) NOT NULL, numero VARCHAR(255) NOT NULL, INDEX IDX_6BB63FF0ECFF285C (table_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emplacement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, nombre_table INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `table` (id INT AUTO_INCREMENT NOT NULL, emplacement_id INT NOT NULL, nom VARCHAR(255) NOT NULL, nombre_chaise INT NOT NULL, INDEX IDX_F6298F46C4598A51 (emplacement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chaise ADD CONSTRAINT FK_6BB63FF0ECFF285C FOREIGN KEY (table_id) REFERENCES `table` (id)');
        $this->addSql('ALTER TABLE `table` ADD CONSTRAINT FK_F6298F46C4598A51 FOREIGN KEY (emplacement_id) REFERENCES emplacement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chaise DROP FOREIGN KEY FK_6BB63FF0ECFF285C');
        $this->addSql('ALTER TABLE `table` DROP FOREIGN KEY FK_F6298F46C4598A51');
        $this->addSql('DROP TABLE chaise');
        $this->addSql('DROP TABLE emplacement');
        $this->addSql('DROP TABLE `table`');
    }
}
